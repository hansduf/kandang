import re
import xml.etree.ElementTree as ET
from typing import List, Tuple, Dict
import os

class ActivityDiagramConverter:
    def __init__(self, puml_content: str):
        self.lines = puml_content.strip().split('\n')
        self.nodes = {}
        self.edges = []
        self.swimlanes = []
        self.current_swimlane = None
        self.node_counter = 0
        self.y_position = {}  # Track Y position per swimlane
        self.swimlane_colors = {
            'Pengguna': '#e1d5e7',
            'Pekerja': '#e1d5e7',
            'Pemilik': '#e1d5e7',
            'User': '#e1d5e7',
            'Sistem': '#d4e6f1',
            'System': '#d4e6f1'
        }
    
    def parse(self):
        """Parse PlantUML activity diagram"""
        stack = []  # To track parent nodes for connections
        
        # Default swimlanes if none defined
        if not self.swimlanes:
            self.swimlanes = ['Sistem']
            self.y_position['Sistem'] = 60
        
        for i, line in enumerate(self.lines):
            line = line.strip()
            
            if not line or line.startswith('@') or line == 'title' in line:
                continue
            
            # Extract swimlane
            if '|' in line and not ':' in line:
                match = re.search(r'\|([^|]+)\|', line)
                if match:
                    swimlane = match.group(1).strip()
                    if swimlane not in self.swimlanes:
                        self.swimlanes.append(swimlane)
                        self.y_position[swimlane] = 60
                    self.current_swimlane = swimlane
            
            # Start node
            elif line == 'start':
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                self.nodes[node_id] = {
                    'type': 'start',
                    'label': '',
                    'swimlane': self.current_swimlane or self.swimlanes[0],
                    'y': 20
                }
                stack.append(node_id)
            
            # End node
            elif line == 'end' or line == 'stop':
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                self.nodes[node_id] = {
                    'type': 'end',
                    'label': '',
                    'swimlane': self.current_swimlane or self.swimlanes[0],
                    'y': 700
                }
                if stack:
                    self.edges.append((stack[-1], node_id, None))
                    stack = [node_id]
            
            # Activity
            elif line.startswith(':') and line.endswith(';'):
                label = line[1:-1].strip()
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                
                swim = self.current_swimlane or self.swimlanes[0]
                y = self.y_position.get(swim, 60)
                
                self.nodes[node_id] = {
                    'type': 'activity',
                    'label': label,
                    'swimlane': swim,
                    'y': y
                }
                self.y_position[swim] = y + 70
                
                if stack:
                    self.edges.append((stack[-1], node_id, None))
                stack = [node_id]
            
            # Decision
            elif line.startswith('if') and '?' in line:
                match = re.search(r'if\s*\(([^)]+)\)', line)
                if match:
                    label = match.group(1).strip()
                    node_id = f'node_{self.node_counter}'
                    self.node_counter += 1
                    
                    swim = self.current_swimlane or self.swimlanes[0]
                    y = self.y_position.get(swim, 60)
                    
                    self.nodes[node_id] = {
                        'type': 'decision',
                        'label': label,
                        'swimlane': swim,
                        'y': y
                    }
                    self.y_position[swim] = y + 90
                    
                    if stack:
                        self.edges.append((stack[-1], node_id, None))
                    stack = [node_id]
            
            # Then branch
            elif 'then' in line and '(' in line:
                match = re.search(r'then\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'Yes'
                if stack:
                    # Mark edge with label
                    last_edge_idx = len(self.edges) - 1
                    if last_edge_idx >= 0 and self.edges[last_edge_idx][2] is None:
                        source, target, _ = self.edges[last_edge_idx]
                        self.edges[last_edge_idx] = (source, target, branch_label)
            
            # Else branch
            elif line.startswith('else'):
                match = re.search(r'else\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'No'
                # This will be connected to the decision node (stack[-1] is decision)
            
            # Endif
            elif line == 'endif':
                if len(stack) > 0:
                    pass  # Stack stays as is
            
            # Repeat
            elif line == 'repeat':
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                self.nodes[node_id] = {
                    'type': 'activity',
                    'label': 'Repeat',
                    'swimlane': self.current_swimlane or self.swimlanes[0],
                    'y': self.y_position.get(self.current_swimlane or self.swimlanes[0], 60)
                }
            
            # Until
            elif 'until' in line:
                match = re.search(r'until\s*\(([^)]+)\)', line)
                if match:
                    label = match.group(1).strip()
                    node_id = f'node_{self.node_counter}'
                    self.node_counter += 1
                    swim = self.current_swimlane or self.swimlanes[0]
                    y = self.y_position.get(swim, 60)
                    
                    self.nodes[node_id] = {
                        'type': 'decision',
                        'label': label,
                        'swimlane': swim,
                        'y': y
                    }
    
    def generate_drawio(self, title: str = 'Activity Diagram') -> str:
        """Generate draw.io XML from parsed diagram"""
        # Create root
        mxfile = ET.Element('mxfile')
        mxfile.set('host', 'app.diagrams.net')
        mxfile.set('modified', '2026-04-13')
        mxfile.set('version', '1.0')
        mxfile.set('type', 'device')
        
        diagram = ET.SubElement(mxfile, 'diagram')
        diagram.set('id', title.lower().replace(' ', '_'))
        diagram.set('name', title)
        
        graph_model = ET.SubElement(diagram, 'mxGraphModel')
        graph_model.set('dx', '1200')
        graph_model.set('dy', '800')
        graph_model.set('grid', '1')
        graph_model.set('gridSize', '10')
        graph_model.set('guides', '1')
        graph_model.set('tooltips', '1')
        graph_model.set('connect', '1')
        graph_model.set('arrows', '1')
        graph_model.set('fold', '1')
        graph_model.set('page', '1')
        graph_model.set('pageScale', '1')
        graph_model.set('pageWidth', '1170')
        graph_model.set('pageHeight', '827')
        graph_model.set('shadow', '0')
        
        root = ET.SubElement(graph_model, 'root')
        
        # Add root cells
        cell0 = ET.SubElement(root, 'mxCell')
        cell0.set('id', '0')
        
        cell1 = ET.SubElement(root, 'mxCell')
        cell1.set('id', '1')
        cell1.set('parent', '0')
        
        # Add swimlanes
        swimlane_width = max(300, 1000 // len(self.swimlanes))
        swimlane_height = 800
        
        swimlane_mapping = {}
        for idx, swimlane in enumerate(self.swimlanes):
            swim_id = f'swim_{idx}'
            swimlane_mapping[swimlane] = swim_id
            
            swim_cell = ET.SubElement(root, 'mxCell')
            swim_cell.set('id', swim_id)
            swim_cell.set('value', swimlane)
            swim_cell.set('style', f'swimlane;startSize=20;horizontal=1;resizeParent=1;fontFamily=Helvetica;fontSize=12;strokeColor=#000000;fillColor={self.swimlane_colors.get(swimlane, "#ffffff")}')
            swim_cell.set('parent', '1')
            swim_cell.set('vertex', '1')
            
            geo = ET.SubElement(swim_cell, 'mxGeometry')
            geo.set('x', str(idx * swimlane_width))
            geo.set('y', '0')
            geo.set('width', str(swimlane_width))
            geo.set('height', str(swimlane_height))
            geo.set('as', 'geometry')
        
        # Add nodes
        for node_id, node_data in self.nodes.items():
            node_type = node_data['type']
            label = node_data['label']
            swim = node_data['swimlane']
            y = node_data['y']
            
            parent_swim = swimlane_mapping.get(swim, 'swim_0')
            
            cell = ET.SubElement(root, 'mxCell')
            cell.set('id', node_id)
            cell.set('value', label)
            cell.set('parent', parent_swim)
            cell.set('vertex', '1')
            
            if node_type == 'start':
                cell.set('style', 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;fillColor=#90EE90;strokeColor=#000000;strokeWidth=2')
                geo = ET.SubElement(cell, 'mxGeometry')
                geo.set('x', '85')
                geo.set('y', '30')
                geo.set('width', '30')
                geo.set('height', '30')
                geo.set('as', 'geometry')
            
            elif node_type == 'end':
                cell.set('style', 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;fillColor=#FF6B6B;strokeColor=#000000;strokeWidth=2')
                geo = ET.SubElement(cell, 'mxGeometry')
                geo.set('x', '85')
                geo.set('y', str(y))
                geo.set('width', '30')
                geo.set('height', '30')
                geo.set('as', 'geometry')
            
            elif node_type == 'decision':
                cell.set('style', 'rhombus;whiteSpace=wrap;html=1;fillColor=#ffe6cc;strokeColor=#000000;fontFamily=Helvetica;fontSize=11')
                geo = ET.SubElement(cell, 'mxGeometry')
                geo.set('x', '50')
                geo.set('y', str(y))
                geo.set('width', '140')
                geo.set('height', '80')
                geo.set('as', 'geometry')
            
            else:  # activity
                cell.set('style', 'rounded=1;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#000000;fontFamily=Helvetica;fontSize=11')
                geo = ET.SubElement(cell, 'mxGeometry')
                geo.set('x', '40')
                geo.set('y', str(y))
                geo.set('width', '160')
                geo.set('height', '50')
                geo.set('as', 'geometry')
        
        # Add edges
        edge_id = 0
        for source, target, label in self.edges:
            edge = ET.SubElement(root, 'mxCell')
            edge.set('id', f'edge_{edge_id}')
            if label:
                edge.set('value', label)
            edge.set('parent', '1')
            edge.set('edge', '1')
            edge.set('source', source)
            edge.set('target', target)
            
            geo = ET.SubElement(edge, 'mxGeometry')
            geo.set('relative', '1')
            geo.set('as', 'geometry')
            
            edge_id += 1
        
        # Convert to string with proper formatting
        tree = ET.ElementTree(mxfile)
        ET.indent(tree, space="    ")
        
        xml_str = ET.tostring(mxfile, encoding='unicode')
        return xml_str


def convert_puml_to_drawio_batch(puml_dir: str, output_dir: str):
    """Convert all PlantUML files to draw.io format"""
    
    files = sorted([f for f in os.listdir(puml_dir) if f.endswith('.puml')])
    
    for puml_file in files:
        puml_path = os.path.join(puml_dir, puml_file)
        drawio_file = puml_file.replace('.puml', '.drawio')
        drawio_path = os.path.join(output_dir, drawio_file)
        
        with open(puml_path, 'r', encoding='utf-8') as f:
            puml_content = f.read()
        
        title = puml_file.replace('.puml', '').replace('-', ' ').title()
        
        try:
            converter = ActivityDiagramConverter(puml_content)
            converter.parse()
            xml_content = converter.generate_drawio(title)
            
            with open(drawio_path, 'w', encoding='utf-8') as f:
                f.write('<?xml version="1.0" encoding="UTF-8"?>\n')
                f.write(xml_content)
            
            print(f'✅ {drawio_file}: {len(converter.nodes)} nodes, {len(converter.edges)} connectors')
        
        except Exception as e:
            print(f'❌ {drawio_file}: {str(e)}')


# Run conversion
puml_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\plant'
output_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\drawio'

convert_puml_to_drawio_batch(puml_dir, output_dir)
print('\n✅ Batch conversion completed!')
