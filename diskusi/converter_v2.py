import re
import xml.etree.ElementTree as ET
from typing import List, Dict, Tuple
import os

class PlantUMLToDIOConverter:
    """Convert PlantUML activity diagrams to draw.io format with proper structure"""
    
    def __init__(self, puml_file: str):
        self.puml_file = puml_file
        self.content = ""
        self.swimlanes = []
        self.activities = []
        self.decisions = {}
        self.edges = []
        self.current_swimlane = None
        self.node_counter = 0
        self.y_positions = {}
        
    def read_file(self):
        """Read PlantUML file"""
        with open(self.puml_file, 'r', encoding='utf-8') as f:
            self.content = f.read()
    
    def parse_puml(self):
        """Parse PlantUML and extract structure"""
        lines = self.content.split('\n')
        stack = []
        
        for line in lines:
            line = line.strip()
            
            # Swimlane
            if '|' in line and not ':' in line:
                match = re.search(r'\|([^|]+)\|', line)
                if match:
                    swimlane = match.group(1).strip()
                    if swimlane not in self.swimlanes:
                        self.swimlanes.append(swimlane)
                        self.y_positions[swimlane] = 85
                    self.current_swimlane = swimlane
            
            # Start
            elif line == 'start':
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                self.activities.append({
                    'id': node_id,
                    'type': 'start',
                    'label': '',
                    'swimlane': self.current_swimlane or self.swimlanes[0] if self.swimlanes else 'System',
                    'y': 26
                })
                stack.append(('activity', node_id))
            
            # End/Stop
            elif line in ['end', 'stop']:
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                self.activities.append({
                    'id': node_id,
                    'type': 'end',
                    'label': '',
                    'swimlane': self.current_swimlane or self.swimlanes[0] if self.swimlanes else 'System',
                    'y': 600
                })
                if stack:
                    prev_type, prev_id = stack[-1]
                    self.edges.append((prev_id, node_id, None, 'orthogonalEdgeStyle'))
                stack = [('activity', node_id)]
            
            # Activity
            elif line.startswith(':') and line.endswith(';'):
                label = line[1:-1].strip()
                node_id = f'node_{self.node_counter}'
                self.node_counter += 1
                
                swim = self.current_swimlane or (self.swimlanes[0] if self.swimlanes else 'System')
                y = self.y_positions.get(swim, 85)
                
                self.activities.append({
                    'id': node_id,
                    'type': 'activity',
                    'label': label,
                    'swimlane': swim,
                    'y': y
                })
                self.y_positions[swim] = y + 60
                
                if stack:
                    prev_type, prev_id = stack[-1]
                    if prev_type == 'decision':
                        # This will be connected based on branch context
                        pass
                    else:
                        self.edges.append((prev_id, node_id, None, 'orthogonalEdgeStyle'))
                
                stack = [('activity', node_id)]
            
            # Decision IF
            elif line.startswith('if') and '?' in line:
                match = re.search(r'if\s*\(([^)]+)\)', line)
                if match:
                    decision_label = match.group(1).strip()
                    node_id = f'node_{self.node_counter}'
                    self.node_counter += 1
                    
                    swim = self.current_swimlane or (self.swimlanes[0] if self.swimlanes else 'System')
                    y = self.y_positions.get(swim, 85)
                    
                    self.decisions[node_id] = {
                        'id': node_id,
                        'type': 'decision',
                        'label': decision_label,
                        'swimlane': swim,
                        'y': y,
                        'branches': {}
                    }
                    self.activities.append(self.decisions[node_id])
                    self.y_positions[swim] = y + 70
                    
                    if stack:
                        prev_type, prev_id = stack[-1]
                        if prev_type != 'decision':
                            self.edges.append((prev_id, node_id, None, 'orthogonalEdgeStyle'))
                    
                    stack = [('decision', node_id)]
            
            # THEN branch
            elif 'then' in line and '(' in line:
                match = re.search(r'then\s*\(([^)]*)\)', line)
                branch = match.group(1).strip() if match else 'Yes'
                if stack and stack[-1][0] == 'decision':
                    _, dec_id = stack[-1]
                    if dec_id in self.decisions:
                        self.decisions[dec_id]['branches'][branch] = {'type': 'true', 'activity': None}
            
            # ELSE branch  
            elif line.startswith('else') and 'if' not in line:
                match = re.search(r'else\s*\(([^)]*)\)', line)
                branch = match.group(1).strip() if match else 'No'
                if stack and stack[-1][0] == 'decision':
                    _, dec_id = stack[-1]
                    if dec_id in self.decisions:
                        self.decisions[dec_id]['branches'][branch] = {'type': 'false', 'activity': None}
        
        # Fill missing swimlanes
        if not self.swimlanes:
            self.swimlanes = ['System']
            self.y_positions['System'] = 85
    
    def generate_drawio(self) -> str:
        """Generate draw.io XML output"""
        # Root elements
        mxfile = ET.Element('mxfile')
        mxfile.set('host', 'app.diagrams.net')
        
        diagram = ET.SubElement(mxfile, 'diagram')
        diagram.set('name', os.path.basename(self.puml_file).replace('.puml', ''))
        diagram.set('id', os.path.basename(self.puml_file).replace('.puml', '').lower().replace('-', '_'))
        
        graph_model = ET.SubElement(diagram, 'mxGraphModel')
        graph_model.set('dx', '1471')
        graph_model.set('dy', '803')
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
        graph_model.set('math', '0')
        graph_model.set('shadow', '0')
        
        root = ET.SubElement(graph_model, 'root')
        
        # Root cells
        cell_0 = ET.SubElement(root, 'mxCell')
        cell_0.set('id', '0')
        
        cell_1 = ET.SubElement(root, 'mxCell')
        cell_1.set('id', '1')
        cell_1.set('parent', '0')
        
        # Create swimlanes (parent container first)
        parent_swim = ET.SubElement(root, 'mxCell')
        parent_id = 'parent_swimlane'
        parent_swim.set('id', parent_id)
        parent_swim.set('parent', '1')
        parent_swim.set('value', 'Activity Diagram')
        parent_swim.set('style', 'swimlane;html=1;childLayout=stackLayout;horizontalStack=0;startSize=20;fillColor=#fff2cc;strokeColor=#d6b656')
        
        geo = ET.SubElement(parent_swim, 'mxGeometry')
        geo.set('width', str(len(self.swimlanes) * 250))
        geo.set('height', '700')
        geo.set('as', 'geometry')
        
        swimlane_map = {}
        swimlane_width = 250
        
        for idx, swimlane_name in enumerate(self.swimlanes):
            swim_id = f'swim_{idx}'
            swimlane_map[swimlane_name] = swim_id
            
            swim = ET.SubElement(root, 'mxCell')
            swim.set('id', swim_id)
            swim.set('parent', parent_id)
            swim.set('value', swimlane_name)
            swim.set('style', f'swimlane;html=1;startSize=20;fillColor=#f5f5f5;fontColor=#333333;strokeColor=#666666')
            
            geo = ET.SubElement(swim, 'mxGeometry')
            geo.set('x', str(idx * swimlane_width))
            geo.set('y', '20')
            geo.set('width', str(swimlane_width))
            geo.set('height', '680')
            geo.set('as', 'geometry')
        
        # Add activities
        for activity in self.activities:
            swim_id = swimlane_map.get(activity['swimlane'], swimlane_map.get(self.swimlanes[0] if self.swimlanes else 'System'))
            
            node = ET.SubElement(root, 'mxCell')
            node.set('id', activity['id'])
            node.set('parent', swim_id)
            node.set('value', activity['label'])
            
            if activity['type'] == 'start':
                node.set('style', 'ellipse;html=1;shape=startState;fillColor=#fff2cc;strokeColor=#d6b656')
                geo = ET.SubElement(node, 'mxGeometry')
                geo.set('x', '68')
                geo.set('y', str(activity['y']))
                geo.set('width', '30')
                geo.set('height', '30')
                geo.set('as', 'geometry')
            
            elif activity['type'] == 'end':
                node.set('style', 'points=[[0.145,0.145,0],[0.5,0,0],[0.855,0.145,0],[1,0.5,0],[0.855,0.855,0],[0.5,1,0],[0.145,0.855,0],[0,0.5,0]];shape=mxgraph.bpmn.event;html=1;verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;perimeter=ellipsePerimeter;outlineConnect=0;aspect=fixed;outline=end;symbol=cancel')
                geo = ET.SubElement(node, 'mxGeometry')
                geo.set('x', '136')
                geo.set('y', str(activity['y']))
                geo.set('width', '34')
                geo.set('height', '34')
                geo.set('as', 'geometry')
            
            elif activity['type'] == 'decision':
                node.set('style', 'rhombus;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#d6b656')
                geo = ET.SubElement(node, 'mxGeometry')
                geo.set('x', '112')
                geo.set('y', str(activity['y']))
                geo.set('width', '80')
                geo.set('height', '40')
                geo.set('as', 'geometry')
            
            else:  # activity
                node.set('style', 'rounded=1;whiteSpace=wrap;html=1;arcSize=40;fillColor=#fff2cc;strokeColor=#d6b656')
                geo = ET.SubElement(node, 'mxGeometry')
                geo.set('x', '23')
                geo.set('y', str(activity['y']))
                geo.set('width', '120')
                geo.set('height', '40')
                geo.set('as', 'geometry')
        
        # Add edges
        for idx, (source_id, target_id, label, style) in enumerate(self.edges):
            edge = ET.SubElement(root, 'mxCell')
            edge.set('id', f'edge_{idx}')
            edge.set('parent', parent_id)
            edge.set('edge', '1')
            edge.set('source', source_id)
            edge.set('target', target_id)
            
            if style == 'orthogonalEdgeStyle':
                edge.set('style', f'edgeStyle=orthogonalEdgeStyle;orthogonalLoop=1;jettySize=auto;html=1;fontSize=12;startSize=8;endSize=8;rounded=0;curved=0')
            else:
                edge.set('style', f'edgeStyle=none;curved=1;orthogonalLoop=1;jettySize=auto;html=1;fontSize=12;startSize=8;endSize=8')
            
            geo = ET.SubElement(edge, 'mxGeometry')
            geo.set('relative', '1')
            geo.set('as', 'geometry')
            
            # Add edge label if exists
            if label:
                label_cell = ET.SubElement(root, 'mxCell')
                label_cell.set('id', f'edge_label_{idx}')
                label_cell.set('parent', f'edge_{idx}')
                label_cell.set('value', label)
                label_cell.set('connectable', '0')
                label_cell.set('style', 'edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];fontSize=11')
                
                label_geo = ET.SubElement(label_cell, 'mxGeometry')
                label_geo.set('relative', '1')
                label_geo.set('x', '-1')
                label_geo.set('y', '0')
                label_geo.set('as', 'geometry')
        
        # Format XML
        ET.indent(root, space="  ")
        xml_str = ET.tostring(mxfile, encoding='unicode')
        
        return f'<?xml version="1.0" encoding="UTF-8"?>\n{xml_str}'

def convert_single_file(puml_file: str, output_file: str):
    """Convert single PlantUML file to draw.io"""
    try:
        converter = PlantUMLToDIOConverter(puml_file)
        converter.read_file()
        converter.parse_puml()
        xml_output = converter.generate_drawio()
        
        with open(output_file, 'w', encoding='utf-8') as f:
            f.write(xml_output)
        
        return True, len(converter.activities), len(converter.edges)
    except Exception as e:
        return False, str(e), None

# Test with single file
if __name__ == '__main__':
    puml_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\plant'
    output_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\drawio'
    
    files = sorted([f for f in os.listdir(puml_dir) if f.endswith('.puml')])
    
    for puml_file in files:
        puml_path = os.path.join(puml_dir, puml_file)
        drawio_file = puml_file.replace('.puml', '.drawio')
        output_path = os.path.join(output_dir, drawio_file)
        
        success, nodes, edges = convert_single_file(puml_path, output_path)
        if success:
            print(f'✅ {drawio_file}: {nodes} nodes, {edges} edges')
        else:
            print(f'❌ {drawio_file}: {nodes}')
