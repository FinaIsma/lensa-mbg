import zipfile
import html
import os

def create_docx(filename, title, sections):
    # XML helper functions
    def w_p(text, bold=False, italic=False, size=22, align="left", space_after=120):
        align_xml = f'<w:jc w:val="{align}"/>' if align != "left" else ''
        bold_xml = '<w:b/>' if bold else ''
        italic_xml = '<w:i/>' if italic else ''
        lines = text.split('\n')
        runs = []
        for i, line in enumerate(lines):
            t_xml = f'<w:t xml:space="preserve">{html.escape(line)}</w:t>'
            br_xml = '<w:br/>' if i > 0 else ''
            runs.append(f'{br_xml}<w:r><w:rPr>{bold_xml}{italic_xml}<w:sz w:val="{size}"/><w:szCs w:val="{size}"/><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/></w:rPr>{t_xml}</w:r>')
        return f'<w:p><w:pPr>{align_xml}<w:spacing w:after="{space_after}"/></w:pPr>{"".join(runs)}</w:p>'

    def w_cell(text, bold=False, italic=False, bg_color=None, width=None, align="left", size=18):
        shd_xml = f'<w:shd w:val="clear" w:color="auto" w:fill="{bg_color}"/>' if bg_color else ''
        width_xml = f'<w:tcW w:w="{width}" w:type="dxa"/>' if width else '<w:tcW w:w="0" w:type="auto"/>'
        borders = '''<w:tcBorders>
            <w:top w:val="single" w:sz="4" w:space="0" w:color="A0A0A0"/>
            <w:left w:val="single" w:sz="4" w:space="0" w:color="A0A0A0"/>
            <w:bottom w:val="single" w:sz="4" w:space="0" w:color="A0A0A0"/>
            <w:right w:val="single" w:sz="4" w:space="0" w:color="A0A0A0"/>
        </w:tcBorders>'''
        mar = '<w:tcMar><w:top w:w="100" w:type="dxa"/><w:bottom w:w="100" w:type="dxa"/><w:left w:w="120" w:type="dxa"/><w:right w:w="120" w:type="dxa"/></w:tcMar>'
        p = w_p(text, bold=bold, italic=italic, size=size, align=align, space_after=40)
        return f'<w:tc><w:tcPr>{width_xml}{shd_xml}{borders}{mar}<w:vAlign w:val="top"/></w:tcPr>{p}</w:tc>'

    def w_table(headers, rows, widths=None, header_bg="2F5597"):
        tbl_pr = '''<w:tblPr>
            <w:tblW w:w="0" w:type="auto"/>
            <w:tblBorders>
                <w:top w:val="single" w:sz="4" w:space="0" w:color="708090"/>
                <w:left w:val="single" w:sz="4" w:space="0" w:color="708090"/>
                <w:bottom w:val="single" w:sz="4" w:space="0" w:color="708090"/>
                <w:right w:val="single" w:sz="4" w:space="0" w:color="708090"/>
                <w:insideH w:val="single" w:sz="4" w:space="0" w:color="D3D3D3"/>
                <w:insideV w:val="single" w:sz="4" w:space="0" w:color="D3D3D3"/>
            </w:tblBorders>
        </w:tblPr>'''
        
        # header row
        h_cells = []
        for i, h in enumerate(headers):
            w = widths[i] if widths and i < len(widths) else None
            h_cells.append(w_cell(h, bold=True, bg_color=header_bg, width=w, align="center", size=18))
        h_row = f'<w:tr><w:trPr><w:tblHeader/></w:trPr>{"".join(h_cells)}</w:tr>'
        
        # data rows
        r_rows = []
        for r_idx, r in enumerate(rows):
            r_cells = []
            row_bg = "F9FAFB" if r_idx % 2 == 1 else "FFFFFF"
            for c_idx, val in enumerate(r):
                w = widths[c_idx] if widths and c_idx < len(widths) else None
                # Check status
                cell_bg = row_bg
                bold = False
                align = "left"
                if val == "PASS":
                    cell_bg = "E2F0D9" # soft green
                    bold = True
                    align = "center"
                elif val == "FAIL":
                    cell_bg = "FCE4D6" # soft red
                    bold = True
                    align = "center"
                elif c_idx == 0 and ("TC_" in str(val) or str(val).isdigit()):
                    align = "center"
                    bold = True
                r_cells.append(w_cell(str(val), bold=bold, bg_color=cell_bg, width=w, align=align, size=16))
            r_rows.append(f'<w:tr>{"".join(r_cells)}</w:tr>')
            
        return f'<w:tbl>{tbl_pr}{h_row}{"".join(r_rows)}</w:tbl>'

    body_xml = []
    
    # Title
    body_xml.append(w_p(title, bold=True, size=32, align="center", space_after=240))
    
    for sec in sections:
        if sec.get("type") == "heading":
            body_xml.append(w_p(sec["text"], bold=True, size=26, space_after=140))
        elif sec.get("type") == "meta":
            # Metadata block
            meta_rows = sec["data"]
            rows_p = []
            for k, v in meta_rows:
                body_xml.append(f'<w:p><w:pPr><w:spacing w:after="40"/></w:pPr><w:r><w:rPr><w:b/><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{html.escape(k)}: </w:t></w:r><w:r><w:rPr><w:sz w:val="20"/></w:rPr><w:t xml:space="preserve">{html.escape(v)}</w:t></w:r></w:p>')
            body_xml.append(w_p("", space_after=100))
        elif sec.get("type") == "table":
            body_xml.append(w_table(sec["headers"], sec["rows"], sec.get("widths"), sec.get("header_bg", "2F5597")))
            body_xml.append(w_p("", space_after=200))
        elif sec.get("type") == "pagebreak":
            body_xml.append('<w:p><w:r><w:br w:type="page"/></w:r></w:p>')
            
    # Landscape orientation for wide tables
    body_xml.append('''<w:sectPr>
        <w:pgSz w:w="16838" w:h="11906" w:orient="landscape"/>
        <w:pgMar w:top="1080" w:right="1080" w:bottom="1080" w:left="1080" w:header="720" w:footer="720" w:gutter="0"/>
    </w:sectPr>''')

    doc_xml = f'''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    {"".join(body_xml)}
  </w:body>
</w:document>'''

    content_types = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>'''

    rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>'''

    doc_rels = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>'''

    styles = '''<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:docDefaults>
    <w:rPrDefault>
      <w:rPr>
        <w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>
        <w:sz w:val="20"/>
      </w:rPr>
    </w:rPrDefault>
  </w:docDefaults>
</w:styles>'''

    with zipfile.ZipFile(filename, 'w', zipfile.ZIP_DEFLATED) as z:
        z.writestr('[Content_Types].xml', content_types)
        z.writestr('_rels/.rels', rels)
        z.writestr('word/_rels/document.xml.rels', doc_rels)
        z.writestr('word/styles.xml', styles)
        z.writestr('word/document.xml', doc_xml)
        
    print(f"File {filename} created successfully ({os.path.getsize(filename)} bytes)")

print("create_docx defined")
