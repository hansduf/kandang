import xml.etree.ElementTree as ET
import os
import re
from datetime import datetime

def create_drawio_from_puml(puml_file, drawio_file):
    """Convert PlantUML activity diagram to draw.io XML format"""
    
    # Read PlantUML file
    with open(puml_file, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract title and swimlanes
    title_match = re.search(r'title\s+(.+?)(?:\n|$)', content)
    title = title_match.group(1) if title_match else os.path.basename(puml_file).replace('.puml', '').replace('-', ' ').title()
    
    # Extract swimlanes (actors like |Pemilik|, |Sistem|, etc.)
    swimlanes = re.findall(r'\|([^|]+)\|', content)
    swimlanes = list(dict.fromkeys(swimlanes))  # Remove duplicates, preserve order
    
    # Extract activities (lines starting with :)
    activities = re.findall(r':([^;:]+);?', content)
    
    # Create root XML structure
    mxfile = ET.Element('mxfile')
    mxfile.set('host', 'app.diagrams.net')
    mxfile.set('modified', datetime.now().strftime('%Y-%m-%d'))
    mxfile.set('version', '1.0')
    mxfile.set('type', 'device')
    mxfile.set('etag', puml_file.split('/')[-1].replace('.puml', ''))
    
    diagram = ET.SubElement(mxfile, 'diagram')
    diagram.set('id', title.replace(' ', '_').lower())
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
    graph_model.set('math', '0')
    graph_model.set('shadow', '0')
    
    root = ET.SubElement(graph_model, 'root')
    
    # Root cells
    cell0 = ET.SubElement(root, 'mxCell')
    cell0.set('id', '0')
    
    cell1 = ET.SubElement(root, 'mxCell')
    cell1.set('id', '1')
    cell1.set('parent', '0')
    
    # Add swimlanes
    swimlane_width = 250
    swimlane_height = 700
    colors = ['#e1d5e7', '#d4e6f1', '#e8f5e9', '#fff3e0', '#fce4ec']
    
    swimlane_cells = {}
    for idx, swimlane in enumerate(swimlanes):
        swim_id = f'swim{idx+1}'
        swimlane_cells[swimlane] = swim_id
        
        swim = ET.SubElement(root, 'mxCell')
        swim.set('id', swim_id)
        swim.set('value', swimlane)
        swim.set('style', f'swimlane;startSize=20;horizontal=1;horizontalStack=0;resizeParent=1;resizeParentMax=0;resizeLast=0;collapsible=0;marginBottom=0;fontFamily=Helvetica;fontSize=12;fontColor=#000000;align=center;strokeColor=#000000;fillColor={colors[idx % len(colors)]}')
        swim.set('parent', '1')
        swim.set('vertex', '1')
        
        geo = ET.SubElement(swim, 'mxGeometry')
        geo.set('x', str(idx * swimlane_width))
        geo.set('y', '0')
        geo.set('width', str(swimlane_width))
        geo.set('height', str(swimlane_height))
        geo.set('as', 'geometry')
    
    # Add start node
    start = ET.SubElement(root, 'mxCell')
    start.set('id', 'start')
    start.set('value', '')
    start.set('style', 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;fillColor=#90EE90;strokeColor=#000000;strokeWidth=2')
    start.set('parent', swimlane_cells.get(swimlanes[0], 'swim1') if swimlanes else '1')
    start.set('vertex', '1')
    
    geo = ET.SubElement(start, 'mxGeometry')
    geo.set('x', '110')
    geo.set('y', '30')
    geo.set('width', '30')
    geo.set('height', '30')
    geo.set('as', 'geometry')
    
    # Add activities
    y_pos = 80
    current_swimlane = swimlanes[0] if swimlanes else 'default'
    
    for idx, activity in enumerate(activities[:20]):  # Limit to first 20 activities
        act_id = f'act{idx+1}'
        
        # Determine swimlane (alternate if we have activities)
        if idx % 2 == 0 and len(swimlanes) > 1:
            current_swimlane = swimlanes[0]
        elif idx % 2 == 1 and len(swimlanes) > 1:
            current_swimlane = swimlanes[1]
        
        act = ET.SubElement(root, 'mxCell')
        act.set('id', act_id)
        act.set('value', activity.strip())
        act.set('style', 'rounded=1;whiteSpace=wrap;html=1;fillColor=#fff2cc;strokeColor=#000000;fontFamily=Helvetica;fontSize=11')
        act.set('parent', swimlane_cells.get(current_swimlane, '1'))
        act.set('vertex', '1')
        
        geo = ET.SubElement(act, 'mxGeometry')
        geo.set('x', '30')
        geo.set('y', str(y_pos))
        geo.set('width', '180')
        geo.set('height', '40')
        geo.set('as', 'geometry')
        
        y_pos += 60
    
    # Add end node
    end = ET.SubElement(root, 'mxCell')
    end.set('id', 'end')
    end.set('value', '')
    end.set('style', 'ellipse;whiteSpace=wrap;html=1;aspect=fixed;fillColor=#FF6B6B;strokeColor=#000000;strokeWidth=2')
    end.set('parent', swimlane_cells.get(swimlanes[0], 'swim1') if swimlanes else '1')
    end.set('vertex', '1')
    
    geo = ET.SubElement(end, 'mxGeometry')
    geo.set('x', '110')
    geo.set('y', str(y_pos + 20))
    geo.set('width', '30')
    geo.set('height', '30')
    geo.set('as', 'geometry')
    
    # Write XML to file
    tree = ET.ElementTree(mxfile)
    ET.indent(tree, space="  ")
    tree.write(drawio_file, encoding='utf-8', xml_declaration=True)
    print(f"✅ Created: {drawio_file}")

# Process all PlantUML files
puml_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\plant'
drawio_dir = r'c:\xampp\htdocs\hans-jaya-poultry\diskusi\drawio'

puml_files = sorted([f for f in os.listdir(puml_dir) if f.endswith('.puml') and f != 'INDEX.md'])

for puml_file in puml_files:
    puml_path = os.path.join(puml_dir, puml_file)
    drawio_file = puml_file.replace('.puml', '.drawio')
    drawio_path = os.path.join(drawio_dir, drawio_file)
    
    try:
        create_drawio_from_puml(puml_path, drawio_path)
    except Exception as e:
        print(f"❌ Error processing {puml_file}: {str(e)}")

print("\n✅ All draw.io files created successfully!")
