import re
import xml.etree.ElementTree as ET
from typing import List, Dict, Tuple
import os

class PumlToDioConverter:
    """Advanced PlantUML to draw.io converter with proper if-else labels"""
    
    def __init__(self, puml_file: str):
        self.puml_file = puml_file
        self.content = ""
        self.swimlanes = []
        self.nodes_dict = {}
        self.edges_list = []
        self.node_counter = 0
        self.y_pos = {}
        
    def read_file(self):
        with open(self.puml_file, 'r', encoding='utf-8') as f:
            self.content = f.read()
    
    def parse_puml(self):
        """Parse PlantUML with proper if-else detection"""
        lines = self.content.split('\n')
        stack = []
        current_swim = None
        decision_stack = []  # Track decision nodes for branch context
        branch_context = 'normal'  # Track if we're in then/else branch
        
        for i, line in enumerate(lines):
            line = line.strip()
            if not line or line.startswith('@'):
                continue
            
            # Swimlane
            if '|' in line:
                match = re.search(r'\|([^|]+)\|', line)
                if match:
                    swim_name = match.group(1).strip()
                    if swim_name not in self.swimlanes:
                        self.swimlanes.append(swim_name)
                        self.y_pos[swim_name] = 85
                    current_swim = swim_name
            
            # Start
            elif line == 'start':
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                self.nodes_dict[nid] = {
                    'type': 'start', 'label': '', 'swim': current_swim or (self.swimlanes[0] if self.swimlanes else 'System'),
                    'y': 26
                }
                stack.append(('node', nid))
            
            # End/Stop
            elif line in ['end', 'stop']:
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                self.nodes_dict[nid] = {'type': 'end', 'label': '', 'swim': swim, 'y': 600}
                if stack:
                    _, prev_id, prev_label = stack.pop() if len(stack[-1]) > 2 else (stack.pop()[0], stack.pop()[1], None)
                    self.edges_list.append((prev_id, nid, prev_label))
                stack = [('node', nid, None)]
            
            # Activity
            elif line.startswith(':') and line.endswith(';'):
                label = line[1:-1].strip()
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                y = self.y_pos.get(swim, 85)
                
                self.nodes_dict[nid] = {'type': 'activity', 'label': label, 'swim': swim, 'y': y}
                self.y_pos[swim] = y + 65
                
                if stack:
                    prev_item = stack.pop()
                    prev_id = prev_item[1]
                    prev_label = prev_item[2] if len(prev_item) > 2 else None
                    
                    # If previous was decision, check branch context
                    if self.nodes_dict[prev_id]['type'] != 'decision':
                        self.edges_list.append((prev_id, nid, prev_label))
                    else:
                        # Add edge from decision with branch label
                        self.edges_list.append((prev_id, nid, prev_label))
                    stack.append(('node', nid, None))
                else:
                    stack = [('node', nid, None)]
            
            # Decision IF
            elif line.startswith('if') and '?' in line:
                match = re.search(r'if\s*\(([^)]+)\)', line)
                if match:
                    label = match.group(1).strip()
                    nid = f'n{self.node_counter}'
                    self.node_counter += 1
                    swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                    y = self.y_pos.get(swim, 85)
                    
                    self.nodes_dict[nid] = {'type': 'decision', 'label': label, 'swim': swim, 'y': y}
                    self.y_pos[swim] = y + 75
                    
                    if stack:
                        prev_item = stack.pop()
                        prev_id = prev_item[1]
                        self.edges_list.append((prev_id, nid, None))
                    
                    stack = [('decision', nid, None)]
                    decision_stack.append({'id': nid, 'then_label': None, 'else_label': None})
            
            # THEN
            elif 'then' in line:
                branch_match = re.search(r'then\s*\(([^)]*)\)', line)
                branch = branch_match.group(1).strip() if branch_match else 'Yes'
                if decision_stack:
                    decision_stack[-1]['then_label'] = branch
                    # Update stack to track we're in then branch
                    if stack and stack[0][0] == 'decision':
                        _, dec_id = stack[0][0], stack[0][1]
                        stack = [('decision', dec_id, branch)]
            
            # ELSE IF
            elif 'else if' in line or line.startswith('else if'):
                branch_match = re.search(r'else\s+if\s*\(([^)]+)\)', line)
                if branch_match and decision_stack:
                    label = branch_match.group(1).strip()
                    # Create new decision node for elseif
                    nid = f'n{self.node_counter}'
                    self.node_counter += 1
                    swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                    y = self.y_pos.get(swim, 85)
                    
                    self.nodes_dict[nid] = {'type': 'decision', 'label': label, 'swim': swim, 'y': y}
                    self.y_pos[swim] = y + 75
                    
                    decision_stack.append({'id': nid, 'then_label': None, 'else_label': None})
            
            # ELSE
            elif line.startswith('else'):
                branch_match = re.search(r'else\s*\(([^)]*)\)', line)
                branch = branch_match.group(1).strip() if branch_match else 'No'
                if decision_stack:
                    decision_stack[-1]['else_label'] = branch
                    # Update stack to track we're in else branch
                    if stack and stack[0][0] == 'decision':
                        _, dec_id = stack[0][0], stack[0][1]
                        stack = [('decision', dec_id, branch)]
            
            # ENDIF
            elif line == 'endif':
                if decision_stack:
                    decision_stack.pop()
                # Reset branch context
                stack = [('node', stack[-1][1] if stack else 'n0', None)]
            
            # Repeat loop
            elif line == 'repeat':
                pass  # For now, treat as regular flow
            
            # Until
            elif 'until' in line:
                pass
        
        # Fill default swimlanes
        if not self.swimlanes:
            self.swimlanes = ['System']
            self.y_pos['System'] = 85
    
    def generate_drawio_xml(self) -> str:
        """Generate proper draw.io XML"""
        mxfile = ET.Element('mxfile')
        mxfile.set('host', 'app.diagrams.net')
        
        diagram = ET.SubElement(mxfile, 'diagram')
        fname = os.path.basename(self.puml_file).replace('.puml', '')
        diagram.set('name', fname)
        diagram.set('id', fname.lower().replace('-', '_'))
        
        graphModel = ET.SubElement(diagram, 'mxGraphModel')
        graphModel.set('dx', '1471')
        graphModel.set('dy', '803')
        graphModel.set('grid', '1')
        graphModel.set('gridSize', '10')
        graphModel.set('guides', '1')
        graphModel.set('tooltips', '1')
        graphModel.set('connect', '1')
        graphModel.set('arrows', '1')
        graphModel.set('fold', '1')
        graphModel.set('page', '1')
        graphModel.set('pageScale', '1')
        graphModel.set('pageWidth', '1170')
        graphModel.set('pageHeight', '827')
        graphModel.set('math', '0')
        graphModel.set('shadow', '0')
        
        root = ET.SubElement(graphModel, 'root')
        
        # Root cells
        ET.SubElement(root, 'mxCell').set('id', '0')
        c1 = ET.SubElement(root, 'mxCell')
        c1.set('id', '1')
        c1.set('parent', '0')
        
        # Parent swimlane container
        parent = ET.SubElement(root, 'mxCell')
        parent.set('id', 'parent_swim')
        parent.set('parent', '1')
        parent.set('value', 'Activity Diagram')
        parent.set('style', 'swimlane;html=1;childLayout=stackLayout;horizontalStack=0;startSize=20;fillColor=#fff2cc;strokeColor=#d6b656')
        
        pgeo = ET.SubElement(parent, 'mxGeometry')
        pgeo.set('width', str(len(self.swimlanes) * 280))
        pgeo.set('height', '700')
        pgeo.set('as', 'geometry')
        
        # Create swimlanes
        swim_map = {}
        for idx, swim_name in enumerate(self.swimlanes):
            swim_id = f'swim_{idx}'
            swim_map[swim_name] = swim_id
            
            swim = ET.SubElement(root, 'mxCell')
            swim.set('id', swim_id)
            swim.set('parent', 'parent_swim')
            swim.set('value', swim_name)
            swim.set('style', 'swimlane;html=1;startSize=20;fillColor=#f5f5f5;fontColor=#333333;strokeColor=#666666')
            
            sgeo = ET.SubElement(swim, 'mxGeometry')
            sgeo.set('x', str(idx * 280))
            sgeo.set('y', '20')
            sgeo.set('width', '280')
            sgeo.set('height', '680')
            sgeo.set('as', 'geometry')
        
        # Add all nodes
        for node_id, node_data in self.nodes_dict.items():
            swim_id = swim_map.get(node_data['swim'], swim_map.get(self.swimlanes[0] if self.swimlanes else 'System'))
            
            cell = ET.SubElement(root, 'mxCell')
            cell.set('id', node_id)
            cell.set('parent', swim_id)
            cell.set('value', node_data['label'])
            
            geo = ET.SubElement(cell, 'mxGeometry')
            
            if node_data['type'] == 'start':
                cell.set('style', 'ellipse;html=1;shape=startState;fillColor=#fff2cc;strokeColor=#d6b656')
                geo.set('x', '125')
                geo.set('y', str(node_data['y']))
                geo.set('width', '30')
                geo.set('height', '30')
            
            elif node_data['type'] == 'end':
                cell.set('style', 'points=[[0.145,0.145,0],[0.5,0,0],[0.855,0.145,0],[1,0.5,0],[0.855,0.855,0],[0.5,1,0],[0.145,0.855,0],[0,0.5,0]];shape=mxgraph.bpmn.event;html=1;verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;perimeter=ellipsePerimeter;outlineConnect=0;aspect=fixed;outline=end;symbol=cancel')
                geo.set('x', '125')
                geo.set('y', str(node_data['y']))
                geo.set('width', '30')
                geo.set('height', '30')
            
            elif node_data['type'] == 'decision':
                cell.set('style', 'rhombus;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#d6b656')
                geo.set('x', '100')
                geo.set('y', str(node_data['y']))
                geo.set('width', '80')
                geo.set('height', '50')
            
            else:  # activity
                cell.set('style', 'rounded=1;whiteSpace=wrap;html=1;arcSize=40;fillColor=#fff2cc;strokeColor=#d6b656')
                geo.set('x', '60')
                geo.set('y', str(node_data['y']))
                geo.set('width', '160')
                geo.set('height', '40')
            
            geo.set('as', 'geometry')
        
        # Add edges with labels
        edge_counter = 0
        for source_id, target_id, label in self.edges_list:
            edge = ET.SubElement(root, 'mxCell')
            edge.set('id', f'edge_{edge_counter}')
            edge.set('parent', 'parent_swim')
            edge.set('edge', '1')
            edge.set('source', source_id)
            edge.set('target', target_id)
            edge.set('style', 'edgeStyle=orthogonalEdgeStyle;orthogonalLoop=1;jettySize=auto;html=1;fontSize=12;startSize=8;endSize=8;rounded=0;curved=0')
            
            egeo = ET.SubElement(edge, 'mxGeometry')
            egeo.set('relative', '1')
            egeo.set('as', 'geometry')
            
            # Add label if exists
            if label:
                elabel = ET.SubElement(root, 'mxCell')
                elabel.set('id', f'label_{edge_counter}')
                elabel.set('parent', f'edge_{edge_counter}')
                elabel.set('value', label)
                elabel.set('connectable', '0')
                elabel.set('style', 'edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];fontSize=11')
                
                lgeo = ET.SubElement(elabel, 'mxGeometry')
                lgeo.set('relative', '1')
                lgeo.set('as', 'geometry')
            
            edge_counter += 1
        
        # Format and return
        ET.indent(root, space="  ")
        return f'<?xml version="1.0" encoding="UTF-8"?>\n' + ET.tostring(mxfile, encoding='unicode')

def convert_batch(puml_dir, output_dir):
    """Convert all PUML files"""
    files = sorted([f for f in os.listdir(puml_dir) if f.endswith('.puml')])
    
    for file in files:
        puml_path = os.path.join(puml_dir, file)
        output_file = file.replace('.puml', '.drawio')
        output_path = os.path.join(output_dir, output_file)
        
        try:
            conv = PumlToDioConverter(puml_path)
            conv.read_file()
            conv.parse_puml()
            xml = conv.generate_drawio_xml()
            
            with open(output_path, 'w', encoding='utf-8') as f:
                f.write(xml)
            
            num_nodes = len(conv.nodes_dict)
            num_edges = len(conv.edges_list)
            print(f'✅ {output_file}: {num_nodes} nodes, {num_edges} edges')
        
        except Exception as e:
            print(f'❌ {output_file}: {str(e)[:50]}')

if __name__ == '__main__':
    puml_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\plant'
    output_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\drawio'
    
    convert_batch(puml_dir, output_dir)
    print('\n✅ Batch conversion complete!')
