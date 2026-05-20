import re
import xml.etree.ElementTree as ET
from typing import List, Dict, Tuple, Optional
import os

class PumlToDioConverter:
    """Advanced PlantUML to draw.io converter with proper branch labels"""
    
    def __init__(self, puml_file: str):
        self.puml_file = puml_file
        self.content = ""
        self.swimlanes = []
        self.nodes_dict = {}
        self.edges_list = []  # List of (source, target, label)
        self.node_counter = 0
        self.y_pos = {}
        
    def read_file(self):
        with open(self.puml_file, 'r', encoding='utf-8') as f:
            self.content = f.read()
    
    def parse_puml(self):
        """Parse PlantUML with proper if-else detection and branch labels"""
        lines = self.content.split('\n')
        current_swim = None
        last_node = None  # Track last node to connect from
        decision_stack = []  # Stack of (decision_node_id, pending_else_connections)
        
        i = 0
        while i < len(lines):
            line = lines[i].strip()
            i += 1
            
            if not line or line.startswith('@') or line.startswith('title'):
                continue
            
            # Swimlane
            if '|' in line and not line.startswith('if') and not line.startswith('else'):
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
                    'type': 'start', 'label': '', 
                    'swim': current_swim or (self.swimlanes[0] if self.swimlanes else 'System'),
                    'y': 26
                }
                last_node = nid
            
            # End/Stop
            elif line in ['end', 'stop']:
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                self.nodes_dict[nid] = {'type': 'end', 'label': '', 'swim': swim, 'y': 600}
                
                if last_node:
                    self.edges_list.append((last_node, nid, None))
                
                last_node = nid
            
            # Activity
            elif line.startswith(':') and line.endswith(';'):
                label = line[1:-1].strip()
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                y = self.y_pos.get(swim, 85)
                
                self.nodes_dict[nid] = {'type': 'activity', 'label': label, 'swim': swim, 'y': y}
                self.y_pos[swim] = y + 65
                
                if last_node:
                    self.edges_list.append((last_node, nid, None))
                
                last_node = nid
            
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
                    
                    if last_node:
                        self.edges_list.append((last_node, nid, None))
                    
                    # Push decision info to stack
                    # Format: (decision_id, list_of_branch_end_nodes)
                    decision_stack.append({
                        'id': nid,
                        'branch_end_nodes': [],
                        'branches': []  # Track branches: [(branch_label, end_node), ...]
                    })
                    
                    last_node = nid
            
            # THEN branch
            elif line.startswith('then'):
                match = re.search(r'then\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'Yes'
                
                if decision_stack:
                    # Mark start of new branch - next nodes will use this label
                    decision_stack[-1]['current_branch_label'] = branch_label
                    decision_stack[-1]['current_branch_start_node'] = last_node
                    # Reset last_node so next activity connects from decision
                    last_node = None
            
            # ELSEIF branch
            elif 'else if' in line or (line.startswith('else') and 'if' in line):
                # End current then branch
                if decision_stack and last_node:
                    decision_stack[-1]['branch_end_nodes'].append({
                        'node': last_node,
                        'label': decision_stack[-1].get('current_branch_label', 'Yes')
                    })
                
                # Parse elseif condition
                match = re.search(r'(?:else\s+)?if\s*\(([^)]+)\)', line)
                if match:
                    condition = match.group(1).strip()
                    nid = f'n{self.node_counter}'
                    self.node_counter += 1
                    swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                    y = self.y_pos.get(swim, 85)
                    
                    self.nodes_dict[nid] = {'type': 'decision', 'label': condition, 'swim': swim, 'y': y}
                    self.y_pos[swim] = y + 75
                    
                    # Connect previous decision to this elseif decision
                    if decision_stack:
                        prev_decision = decision_stack[-1]['id']
                        branch_match = re.search(r'then\s*\(([^)]*)\)', line)
                        elseif_label = branch_match.group(1).strip() if branch_match else None
                        elseif_label = re.search(r'else\s+if[^(]*\(([^)]+)\)', line)
                        if elseif_label:
                            elseif_connector_label = 'Tidak'
                        else:
                            elseif_connector_label = None
                        
                        self.edges_list.append((prev_decision, nid, elseif_connector_label))
                    
                    # Start new decision_stack entry for this elseif
                    decision_stack.append({
                        'id': nid,
                        'branch_end_nodes': [],
                        'branches': []
                    })
                    
                    last_node = nid
                
                # Now look for the then part
                match = re.search(r'then\s*\(([^)]*)\)', line)
                if match:
                    branch_label = match.group(1).strip()
                    if decision_stack:
                        decision_stack[-1]['current_branch_label'] = branch_label
                    last_node = None
            
            # ELSE branch
            elif line.startswith('else'):
                # End current then branch
                if decision_stack and last_node:
                    current_label = decision_stack[-1].get('current_branch_label', 'Yes')
                    decision_stack[-1]['branch_end_nodes'].append({
                        'node': last_node,
                        'label': current_label
                    })
                
                # Parse else label
                match = re.search(r'else\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'No'
                
                if decision_stack:
                    decision_stack[-1]['current_branch_label'] = branch_label
                
                last_node = None
            
            # ENDIF
            elif line == 'endif':
                if decision_stack:
                    # Add final branch's end node
                    if last_node:
                        current_label = decision_stack[-1].get('current_branch_label', 'No')
                        decision_stack[-1]['branch_end_nodes'].append({
                            'node': last_node,
                            'label': current_label
                        })
                    
                    # Add edges from decision to branch end nodes
                    decision_info = decision_stack.pop()
                    dec_id = decision_info['id']
                    
                    for branch_info in decision_info['branch_end_nodes']:
                        branch_node = branch_info['node']
                        branch_label = branch_info['label']
                        
                        if branch_node and branch_node != dec_id:
                            self.edges_list.append((dec_id, branch_node, branch_label))
                    
                    # After endif, all branches converge - find last node from all branches
                    if decision_info['branch_end_nodes']:
                        # Track all branch end nodes for later convergence
                        last_node = dec_id  # Will be picked up by next activity
                    else:
                        last_node = dec_id
            
            # Repeat
            elif line == 'repeat':
                pass
            
            # Until
            elif 'until' in line:
                pass
        
        # Fill default swimlanes if empty
        if not self.swimlanes:
            self.swimlanes = ['System']
            self.y_pos['System'] = 85
    
    def generate_drawio_xml(self) -> str:
        """Generate proper draw.io XML with nested swimlanes"""
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
        parent.set('vertex', '1')
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
            swim.set('vertex', '1')
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
            cell.set('vertex', '1')
            
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
            edge.set('source', source_id)
            edge.set('target', target_id)
            edge.set('edge', '1')
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
                elabel.set('vertex', '1')
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
