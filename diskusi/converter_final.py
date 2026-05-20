import re
import xml.etree.ElementTree as ET
from typing import List, Dict, Tuple, Optional
import os

class PumlToDioConverter:
    """PlantUML to draw.io converter - Proper merge handling"""
    
    def __init__(self, puml_file: str):
        self.puml_file = puml_file
        self.content = ""
        self.swimlanes = []
        self.nodes_dict = {}
        self.edges_list = []
        self.node_counter = 0
        self.y_pos = {}
        self.merge_branches = []  # Track branches awaiting merge
        
    def read_file(self):
        with open(self.puml_file, 'r', encoding='utf-8') as f:
            self.content = f.read()
    
    def parse_puml(self):
        """Parse PlantUML dengan proper branch + merge handling"""
        lines = self.content.split('\n')
        current_swim = None
        last_node = None
        decision_stack = []
        
        for line in lines:
            line = line.strip()
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
            
            elif line == 'start':
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                self.nodes_dict[nid] = {'type': 'start', 'label': '', 'swim': swim, 'y': 26}
                last_node = nid
            
            elif line in ['end', 'stop']:
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                self.nodes_dict[nid] = {'type': 'end', 'label': '', 'swim': swim, 'y': 585}
                
                # Connect from merge branches if any
                if self.merge_branches:
                    for branch_end in self.merge_branches:
                        if branch_end:
                            self.edges_list.append((branch_end, nid, None))
                    self.merge_branches = []
                elif last_node and not decision_stack:
                    self.edges_list.append((last_node, nid, None))
                
                last_node = nid
            
            elif line.startswith(':') and line.endswith(';'):
                label = line[1:-1].strip()
                nid = f'n{self.node_counter}'
                self.node_counter += 1
                swim = current_swim or (self.swimlanes[0] if self.swimlanes else 'System')
                y = self.y_pos.get(swim, 85)
                
                self.nodes_dict[nid] = {'type': 'activity', 'label': label, 'swim': swim, 'y': y}
                self.y_pos[swim] = y + 65
                
                # Track branch starts in decision stack
                if decision_stack:
                    dec = decision_stack[-1]
                    if 'branch_mode' not in dec:
                        dec['branch_mode'] = 'normal'
                    
                    if dec.get('branch_mode') == 'then' and not dec.get('then_start'):
                        dec['then_start'] = nid
                    elif dec.get('branch_mode') == 'else' and not dec.get('else_start'):
                        dec['else_start'] = nid
                
                # Connect from last_node if not in merge state
                if last_node and not self.merge_branches:
                    self.edges_list.append((last_node, nid, None))
                
                last_node = nid
            
            elif line.startswith('if') and '?' in line:
                # Parse: if (condition?) then (label_ya)
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
                    
                    # Extract then label from same line if present
                    then_match = re.search(r'then\s*\(([^)]*)\)', line)
                    then_label = then_match.group(1).strip() if then_match else 'Ya'
                    
                    decision_stack.append({
                        'id': nid,
                        'then_start': None,
                        'then_end': None,
                        'else_start': None,
                        'else_end': None,
                        'then_label': then_label,
                        'else_label': 'Tidak',
                        'branch_mode': 'then'  # Set to 'then' immediately
                    })
                    last_node = None
            
            elif line.startswith('then'):
                match = re.search(r'then\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'Ya'
                if decision_stack:
                    decision_stack[-1]['then_label'] = branch_label
                    decision_stack[-1]['branch_mode'] = 'then'
                    last_node = None
            
            elif line.startswith('else'):
                if decision_stack:
                    if last_node:
                        decision_stack[-1]['then_end'] = last_node
                    decision_stack[-1]['branch_mode'] = 'else'
                
                match = re.search(r'else\s*\(([^)]*)\)', line)
                branch_label = match.group(1).strip() if match else 'Tidak'
                if decision_stack:
                    decision_stack[-1]['else_label'] = branch_label
                    last_node = None
            
            elif line == 'endif':
                if decision_stack:
                    if last_node:
                        decision_stack[-1]['else_end'] = last_node
                    
                    dec_info = decision_stack.pop()
                    dec_id = dec_info['id']
                    
                    then_start = dec_info.get('then_start')
                    else_start = dec_info.get('else_start')
                    then_end = dec_info.get('then_end')
                    else_end = dec_info.get('else_end')
                    
                    # Create edges from decision to branch starts
                    if then_start:
                        self.edges_list.append((dec_id, then_start, dec_info['then_label']))
                    
                    if else_start:
                        self.edges_list.append((dec_id, else_start, dec_info['else_label']))
                    
                    # Set up merge: both branch ends need to connect to next node
                    if then_end and else_end:
                        self.merge_branches = [then_end, else_end]
                        last_node = None
                    elif then_end:
                        self.merge_branches = [then_end]
                        last_node = None
                    elif else_end:
                        self.merge_branches = [else_end]
                        last_node = None
                    else:
                        last_node = dec_id
        
        if not self.swimlanes:
            self.swimlanes = ['System']
            self.y_pos['System'] = 85
    
    def generate_drawio_xml(self) -> str:
        """Generate draw.io XML"""
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
        graphModel.set('pageWidth', '827')
        graphModel.set('pageHeight', '1169')
        graphModel.set('math', '0')
        graphModel.set('shadow', '0')
        
        root = ET.SubElement(graphModel, 'root')
        
        ET.SubElement(root, 'mxCell').set('id', '0')
        c1 = ET.SubElement(root, 'mxCell')
        c1.set('id', '1')
        c1.set('parent', '0')
        
        # Parent swimlane
        parent = ET.SubElement(root, 'mxCell')
        parent.set('id', 'parent_swim')
        parent.set('parent', '1')
        parent.set('value', f'Activity diagram {fname.replace("-", " ")}')
        parent.set('vertex', '1')
        parent.set('style', 'swimlane;html=1;childLayout=stackLayout;resizeParent=1;resizeParentMax=0;startSize=20;whiteSpace=wrap;align=left;movable=1;resizable=1;rotatable=1;deletable=1;editable=1;locked=0;connectable=1;fillColor=#fff2cc;strokeColor=#d6b656')
        
        pgeo = ET.SubElement(parent, 'mxGeometry')
        swim_width = sum([160, 200][1:]) + 150 if len(self.swimlanes) > 1 else 280
        pgeo.set('height', '662')
        pgeo.set('width', str(swim_width + 50))
        pgeo.set('x', '20')
        pgeo.set('y', '40')
        pgeo.set('as', 'geometry')
        
        # Create swimlanes
        swim_map = {}
        swim_widths = [212, 333, 200, 200]
        x_offset = 0
        
        for idx, swim_name in enumerate(self.swimlanes):
            swim_id = f'swim_{idx}'
            swim_map[swim_name] = swim_id
            swim_width = swim_widths[idx] if idx < len(swim_widths) else 200
            
            swim = ET.SubElement(root, 'mxCell')
            swim.set('id', swim_id)
            swim.set('parent', 'parent_swim')
            swim.set('value', swim_name)
            swim.set('vertex', '1')
            swim.set('style', 'swimlane;html=1;startSize=20;fillColor=#f5f5f5;fontColor=#333333;strokeColor=#666666')
            
            sgeo = ET.SubElement(swim, 'mxGeometry')
            sgeo.set('x', str(x_offset))
            sgeo.set('y', '20')
            sgeo.set('width', str(swim_width))
            sgeo.set('height', '642')
            sgeo.set('as', 'geometry')
            
            x_offset += swim_width
        
        # Add nodes
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
                geo.set('x', '68')
                geo.set('y', '26')
                geo.set('width', '30')
                geo.set('height', '30')
            
            elif node_data['type'] == 'end':
                cell.set('style', 'points=[[0.145,0.145,0],[0.5,0,0],[0.855,0.145,0],[1,0.5,0],[0.855,0.855,0],[0.5,1,0],[0.145,0.855,0],[0,0.5,0]];shape=mxgraph.bpmn.event;html=1;verticalLabelPosition=bottom;labelBackgroundColor=#ffffff;verticalAlign=top;align=center;perimeter=ellipsePerimeter;outlineConnect=0;aspect=fixed;outline=end;symbol=cancel')
                geo.set('x', '91')
                geo.set('y', str(node_data['y']))
                geo.set('width', '34')
                geo.set('height', '34')
            
            elif node_data['type'] == 'decision':
                cell.set('style', 'rhombus;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#d6b656')
                geo.set('x', '76')
                geo.set('y', str(node_data['y']))
                geo.set('width', '80')
                geo.set('height', '40')
            
            else:  # activity
                cell.set('style', 'rounded=1;whiteSpace=wrap;html=1;arcSize=40;fillColor=#fff2cc;strokeColor=#d6b656')
                geo.set('x', '23')
                geo.set('y', str(node_data['y']))
                geo.set('width', '120')
                geo.set('height', '40')
            
            geo.set('as', 'geometry')
        
        # Add edges
        edge_counter = 0
        for source_id, target_id, label in self.edges_list:
            source = self.nodes_dict.get(source_id)
            target = self.nodes_dict.get(target_id)
            
            if not source or not target:
                continue
            
            edge = ET.SubElement(root, 'mxCell')
            edge.set('id', f'edge_{edge_counter}')
            edge.set('parent', 'parent_swim')
            edge.set('source', source_id)
            edge.set('target', target_id)
            edge.set('edge', '1')
            
            # Edge styling
            if source['swim'] != target['swim']:
                edge.set('style', 'edgeStyle=none;curved=1;orthogonalLoop=1;jettySize=auto;html=1;entryX=0;entryY=0.5;entryDx=0;entryDy=0;fontSize=12;startSize=8;endSize=8')
            else:
                edge.set('style', 'edgeStyle=none;curved=1;orthogonalLoop=1;jettySize=auto;html=1;fontSize=12;startSize=8;endSize=8')
            
            egeo = ET.SubElement(edge, 'mxGeometry')
            egeo.set('relative', '1')
            egeo.set('as', 'geometry')
            
            # Add label
            if label:
                elabel = ET.SubElement(root, 'mxCell')
                elabel.set('id', f'label_{edge_counter}')
                elabel.set('parent', f'edge_{edge_counter}')
                elabel.set('value', label)
                elabel.set('connectable', '0')
                elabel.set('vertex', '1')
                elabel.set('style', 'edgeLabel;html=1;align=center;verticalAlign=middle;resizable=0;points=[];fontSize=12')
                
                lgeo = ET.SubElement(elabel, 'mxGeometry')
                lgeo.set('relative', '1')
                lgeo.set('as', 'geometry')
            
            edge_counter += 1
        
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
            print(f'❌ {output_file}: {str(e)[:80]}')

if __name__ == '__main__':
    puml_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\plant'
    output_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\drawio'
    
    convert_batch(puml_dir, output_dir)
    print('\n✅ Batch conversion complete!')
