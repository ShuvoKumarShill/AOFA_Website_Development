#!/usr/bin/env python3
"""
Generate a beautiful, executive-grade PDF of the AOFA Website Admin Manual
using Headless Google Chrome with embedded base64 screenshots and custom styling.
"""

import base64
import os
import subprocess
import sys

BASE_DIR = "/home/shuvo/github/AOFA_Website_Development"
DOCS_DIR = os.path.join(BASE_DIR, "docs")
IMAGES_DIR = os.path.join(DOCS_DIR, "images")
OUTPUT_HTML = os.path.join(DOCS_DIR, "admin_manual_print.html")
OUTPUT_PDF = os.path.join(DOCS_DIR, "AOFA_Website_Admin_Manual.pdf")
ROOT_PDF = os.path.join(BASE_DIR, "AOFA_Website_Admin_Manual.pdf")
ARTIFACT_PDF = "/home/shuvo/.gemini/antigravity-ide/brain/c89f4881-a65e-4a6b-8234-b716fe107612/AOFA_Website_Admin_Manual.pdf"

def img_to_base64(filename):
    path = os.path.join(IMAGES_DIR, filename)
    if not os.path.exists(path):
        print(f"Warning: Image not found {path}")
        return ""
    with open(path, "rb") as f:
        encoded = base64.b64encode(f.read()).decode("utf-8")
    return f"data:image/png;base64,{encoded}"

def main():
    print("Encoding screenshots...")
    images = {
        "01_login": img_to_base64("01_login_screen.png"),
        "02_dashboard": img_to_base64("02_admin_dashboard.png"),
        "03_pages": img_to_base64("03_pages_list.png"),
        "04_edit_page": img_to_base64("04_editing_page.png"),
        "05_members": img_to_base64("05_members_list.png"),
        "06_member_meta": img_to_base64("06_member_details.png"),
        "07_ec": img_to_base64("07_ec_list.png"),
        "08_notice": img_to_base64("08_notice_edit.png"),
        "09_article": img_to_base64("09_article_details.png"),
        "10_gallery": img_to_base64("10_photo_gallery.png"),
        "11_menu": img_to_base64("11_menu_management.png"),
        "12_media": img_to_base64("12_media_library.png"),
    }

    print("Building HTML template...")
    html_content = f"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>AOFA Website Administration Manual</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap');

  @page {{
    size: A4 portrait;
    margin: 20mm 15mm 20mm 15mm;
    @bottom-right {{
      content: counter(page);
    }}
  }}

  * {{
    box-sizing: border-box;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }}

  body {{
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    color: #1e293b;
    line-height: 1.6;
    font-size: 10pt;
    margin: 0;
    padding: 0;
    background: #ffffff;
  }}

  /* Headings */
  h1, h2, h3, h4 {{
    color: #002B49;
    font-family: 'Inter', sans-serif;
    font-weight: 700;
    margin-top: 1.4em;
    margin-bottom: 0.5em;
    line-height: 1.25;
  }}

  h1 {{ font-size: 22pt; }}
  h2 {{ 
    font-size: 15pt; 
    border-bottom: 2px solid #C5A059;
    padding-bottom: 4px;
    margin-top: 1.8em;
  }}
  h3 {{ font-size: 12pt; color: #0f172a; }}
  h4 {{ font-size: 10.5pt; color: #334155; }}

  p {{
    margin: 0.6em 0;
  }}

  /* Cover Page */
  .cover-page {{
    page-break-after: always;
    height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 40px 20px;
    box-sizing: border-box;
    text-align: center;
    border: 12px double #002B49;
    background: linear-gradient(180deg, #F8FAFC 0%, #FFFFFF 100%);
  }}

  .cover-header {{
    margin-top: 40px;
  }}

  .crest-badge {{
    width: 90px;
    height: 90px;
    margin: 0 auto 20px auto;
    background: #002B49;
    border: 3px solid #C5A059;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #C5A059;
    font-size: 36pt;
    font-weight: 800;
    letter-spacing: 2px;
  }}

  .cover-title {{
    font-size: 26pt;
    color: #002B49;
    font-weight: 800;
    letter-spacing: -0.5px;
    margin: 15px 0 10px 0;
    text-transform: uppercase;
  }}

  .cover-subtitle {{
    font-size: 14pt;
    color: #C5A059;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    margin-bottom: 20px;
  }}

  .cover-tagline {{
    font-size: 12pt;
    color: #475569;
    max-width: 500px;
    margin: 0 auto;
    font-style: italic;
  }}

  .cover-meta {{
    background: #002B49;
    color: #FFFFFF;
    padding: 20px;
    border-radius: 8px;
    margin: 40px auto 20px auto;
    width: 80%;
    border-left: 6px solid #C5A059;
    text-align: left;
    font-size: 9.5pt;
  }}

  .cover-meta table {{
    width: 100%;
    border-collapse: collapse;
  }}

  .cover-meta td {{
    padding: 4px 8px;
    border: none;
    color: #E2E8F0;
  }}

  .cover-meta td strong {{
    color: #FFFFFF;
  }}

  .cover-footer {{
    margin-bottom: 20px;
    font-size: 9pt;
    color: #64748B;
    border-top: 1px solid #CBD5E1;
    padding-top: 15px;
  }}

  /* Page Break Control */
  .page-break {{
    page-break-before: always;
  }}

  .avoid-break {{
    page-break-inside: avoid;
  }}

  /* Tables */
  table.data-table {{
    width: 100%;
    border-collapse: collapse;
    margin: 15px 0;
    font-size: 9pt;
    page-break-inside: avoid;
  }}

  table.data-table th {{
    background: #002B49;
    color: #FFFFFF;
    font-weight: 600;
    text-align: left;
    padding: 8px 10px;
    border: 1px solid #002B49;
  }}

  table.data-table td {{
    padding: 7px 10px;
    border: 1px solid #CBD5E1;
  }}

  table.data-table tr:nth-child(even) {{
    background: #F8FAFC;
  }}

  /* Callouts & Alert Boxes */
  .callout {{
    padding: 12px 16px;
    border-radius: 6px;
    margin: 14px 0;
    font-size: 9.5pt;
    page-break-inside: avoid;
  }}

  .callout-info {{
    background: #EFF6FF;
    border-left: 4px solid #2563EB;
    color: #1E40AF;
  }}

  .callout-warning {{
    background: #FFFBEB;
    border-left: 4px solid #D97706;
    color: #92400E;
  }}

  .callout-tip {{
    background: #F0FDF4;
    border-left: 4px solid #16A34A;
    color: #166534;
  }}

  .callout strong {{
    font-weight: 700;
  }}

  /* Screenshots & Figures */
  .figure-card {{
    margin: 15px 0;
    border: 1px solid #CBD5E1;
    border-radius: 6px;
    overflow: hidden;
    background: #FFFFFF;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
    page-break-inside: avoid;
    text-align: center;
  }}

  .figure-card img {{
    width: 100%;
    max-height: 380px;
    object-fit: contain;
    background: #F1F5F9;
    display: block;
    border-bottom: 1px solid #E2E8F0;
  }}

  .figure-caption {{
    padding: 6px 12px;
    font-size: 8.5pt;
    font-weight: 600;
    color: #475569;
    background: #F8FAFC;
  }}

  /* Steps Lists */
  ol.steps {{
    margin: 10px 0;
    padding-left: 22px;
  }}

  ol.steps > li {{
    margin-bottom: 8px;
    line-height: 1.5;
  }}

  ul.bullets {{
    margin: 10px 0;
    padding-left: 20px;
  }}

  ul.bullets > li {{
    margin-bottom: 5px;
  }}

  .badge {{
    display: inline-block;
    padding: 2px 7px;
    font-size: 8pt;
    font-weight: 700;
    border-radius: 4px;
    text-transform: uppercase;
    background: #002B49;
    color: #FFFFFF;
    margin-right: 4px;
  }}

  .badge-gold {{
    background: #C5A059;
    color: #002B49;
  }}

  .badge-sub {{
    background: #0284C7;
    color: #FFFFFF;
  }}

  /* Header / Footer line */
  .section-tag {{
    font-size: 8pt;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    color: #C5A059;
    margin-bottom: 2px;
  }}
</style>
</head>
<body>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- COVER PAGE                                                       -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="cover-page">
  <div class="cover-header">
    <div class="crest-badge">&#9733;</div>
    <div class="cover-subtitle">Association of Former BCS(FA) Ambassadors</div>
    <div class="cover-title">Website Administration<br>Manual</div>
    <p class="cover-tagline">
      A Step-by-Step, Non-Technical Guide for Easy Content Management,<br>
      Member Directory Updates, and Diplomatic Secretariat Operations
    </p>
  </div>

  <div class="cover-meta">
    <table>
      <tr>
        <td style="width: 35%;"><strong>Target Audience:</strong></td>
        <td>AOFA Office Holders, Executive Committee & Secretariat Staff</td>
      </tr>
      <tr>
        <td><strong>Prerequisites:</strong></td>
        <td>Zero Coding Knowledge Required (100% Visual Forms & Editor)</td>
      </tr>
      <tr>
        <td><strong>Website Portal:</strong></td>
        <td>https://aofabd.org (WordPress Executive Edition)</td>
      </tr>
      <tr>
        <td><strong>Document Version:</strong></td>
        <td>Version 1.0 (Official Release — October 2026)</td>
      </tr>
      <tr>
        <td><strong>Author / Maintainer:</strong></td>
        <td>AOFA Web Development Team</td>
      </tr>
    </table>
  </div>

  <div class="cover-footer">
    &copy; 2026 Association of Former BCS(FA) Ambassadors (AOFA) &bull; Dhaka, Bangladesh &bull; All Rights Reserved
  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- QUICK REFERENCE CHEAT SHEET                                      -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div>
  <div class="section-tag">Quick Start</div>
  <h2>Quick Reference Cheat Sheet</h2>
  <p>Use this table to immediately locate where to make routine website updates without guessing.</p>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 32%;">Routine Administrative Task</th>
        <th style="width: 36%;">Where to Go in Left Sidebar</th>
        <th style="width: 32%;">Key Button / Action</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Edit Text on Main Pages</strong><br><small>(Home, About, Messages, Constitution, Contact)</small></td>
        <td><strong>Pages</strong> &rarr; <strong>All Pages</strong></td>
        <td>Click page title &rarr; edit text &rarr; click <strong>Update</strong></td>
      </tr>
      <tr>
        <td><strong>Add a New Ambassador</strong></td>
        <td><strong>Members</strong> &rarr; <strong>Add New Member</strong></td>
        <td>Fill Name, Address, Phone, Email &rarr; click <strong>Publish</strong></td>
      </tr>
      <tr>
        <td><strong>Update EC Leadership List</strong></td>
        <td><strong>Executive Committee</strong> &rarr; <strong>Add New EC Member</strong></td>
        <td>Enter Name, Serial, Position &amp; check Term &rarr; click <strong>Publish</strong></td>
      </tr>
      <tr>
        <td><strong>Post an Official Notice / Circular</strong></td>
        <td><strong>Notices</strong> &rarr; <strong>Add New Notice</strong></td>
        <td>Enter title, paste text, attach PDF link &rarr; click <strong>Publish</strong></td>
      </tr>
      <tr>
        <td><strong>Publish an Article or Book Review</strong></td>
        <td><strong>Articles &amp; Books</strong> &rarr; <strong>Add New Article</strong></td>
        <td>Fill author, source, language, body &rarr; click <strong>Publish</strong></td>
      </tr>
      <tr>
        <td><strong>Upload Photos to Photo Gallery</strong></td>
        <td><strong>Photo Gallery</strong> &rarr; <strong>Add New Photo</strong></td>
        <td>Click <strong>Select / Upload Multiple Photos</strong> &rarr; click <strong>Publish</strong></td>
      </tr>
      <tr>
        <td><strong>Upload a PDF Document</strong></td>
        <td><strong>Media</strong> &rarr; <strong>Add Media File</strong></td>
        <td>Upload file &rarr; click <strong>Copy URL to clipboard</strong></td>
      </tr>
      <tr>
        <td><strong>Edit the Top Navigation Bar</strong></td>
        <td><strong>Appearance</strong> &rarr; <strong>Menus</strong></td>
        <td>Drag &amp; drop menu cards &rarr; click <strong>Save Menu</strong></td>
      </tr>
      <tr>
        <td><strong>Add New Staff Account</strong></td>
        <td><strong>Users</strong> &rarr; <strong>Add New User</strong></td>
        <td>Fill email &amp; name &rarr; choose role <strong>Editor</strong> &rarr; save</td>
      </tr>
    </tbody>
  </table>

  <div class="callout callout-info">
    <strong>Golden Rule of Web Editing:</strong> Whenever you make a change, it will only become visible to the public once you click the blue <strong>Update</strong> or <strong>Publish</strong> button at the top right of your screen.
  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 1: GETTING STARTED & LOGGING IN                          -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 1</div>
  <h2>Getting Started &amp; Logging In</h2>

  <h3>1.1 Accessing the Administration Portal</h3>
  <p>The AOFA website administration portal is accessible through any modern web browser (Google Chrome, Microsoft Edge, Safari, or Firefox).</p>

  <ol class="steps">
    <li>Open your browser and navigate to the admin address:<br>
        <strong>Live Production Site:</strong> <a href="https://aofabd.org/wp-admin">https://aofabd.org/wp-admin</a><br>
        <em>(Local Development Site: <code>http://localhost:8080/wp-admin</code>)</em>
    </li>
    <li>The secure login screen will appear:</li>
  </ol>

  <div class="figure-card">
    <img src="{images['01_login']}" alt="AOFA Admin Login Screen">
    <div class="figure-caption">Figure 1.1: Official AOFA WordPress Portal Login Screen</div>
  </div>

  <ol class="steps" start="3">
    <li>Enter your assigned <strong>Username or Email Address</strong>.</li>
    <li>Enter your secure <strong>Password</strong>.</li>
    <li><em>(Optional)</em> Check <strong>Remember Me</strong> if on a private, trusted computer.</li>
    <li>Click the blue <strong>Log In</strong> button.</li>
  </ol>

  <h3>1.2 Navigating the Admin Dashboard</h3>
  <p>After logging in, your <strong>Admin Dashboard</strong> will open. This is your mission control center.</p>

  <div class="figure-card">
    <img src="{images['02_dashboard']}" alt="AOFA Admin Dashboard">
    <div class="figure-caption">Figure 1.2: WordPress Administration Dashboard with Left Navigation Panel</div>
  </div>

  <p>The dashboard has three primary zones:</p>
  <ul class="bullets">
    <li><strong>Left Navigation Sidebar (Dark Charcoal):</strong> Contains all content management sections (Members, Notices, Articles &amp; Books, Photo Gallery, Pages, Media, Appearance).</li>
    <li><strong>Top Black Bar (Admin Bar):</strong> Shows a home link to view the public website, and your account menu on the far right.</li>
    <li><strong>Main Work Area (Center):</strong> Where you enter information, edit pages, and review member directories.</li>
  </ul>

  <div class="avoid-break">
    <h3>1.3 Resetting Your Password</h3>
    <p>If you forget your password, click <strong>"Lost your password?"</strong> on the login screen, enter your registered email address, and an automated reset link will be sent to your inbox.</p>

    <h3>1.4 Safe Log Out</h3>
    <p>To safely sign out, hover your mouse over <strong>"Howdy, [Your Name]"</strong> at the top right of the screen, and click <strong>Log Out</strong>.</p>
  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 2: EDITING WEBSITE PAGES & TEXT                          -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 2</div>
  <h2>Editing Website Pages &amp; Text</h2>

  <h3>2.1 Finding the Page to Edit</h3>
  <p>Informational pages (such as <strong>About AOFA</strong>, <strong>Constitution</strong>, <strong>President's Messages</strong>, and <strong>Contact Us</strong>) are stored in the Pages section.</p>

  <ol class="steps">
    <li>Click <strong>Pages</strong> &rarr; <strong>All Pages</strong> in the left sidebar.</li>
    <li>You will see a list of all existing website pages:</li>
  </ol>

  <div class="figure-card">
    <img src="{images['03_pages']}" alt="Pages List">
    <div class="figure-caption">Figure 2.1: Master Pages List in WordPress Admin</div>
  </div>

  <ol class="steps" start="3">
    <li>Hover over the name of the page you want to update (e.g. <em>About AOFA</em>).</li>
    <li>Click on the <strong>Page Title</strong> or the <strong>Edit</strong> link.</li>
  </ol>

  <h3>2.2 The Visual Block Editor</h3>
  <p>WordPress uses an intuitive visual editor where each paragraph, heading, or image is an editable block.</p>

  <div class="figure-card">
    <img src="{images['04_edit_page']}" alt="Editing Page in Block Editor">
    <div class="figure-caption">Figure 2.2: Visual Block Editor Interface showing text blocks and right-hand settings</div>
  </div>

  <h3>2.3 How to Format Text</h3>
  <ul class="bullets">
    <li><strong>Typing &amp; Editing:</strong> Click directly into any text block and type or delete words just like in Microsoft Word.</li>
    <li><strong>Bold or Italic:</strong> Highlight words and click <strong>B</strong> or <em>I</em> on the floating toolbar.</li>
    <li><strong>Adding New Blocks:</strong> Click the blue <strong>(+)</strong> button at the top left to add a <strong>Paragraph</strong>, <strong>Heading</strong>, or <strong>List</strong>.</li>
    <li><strong>Adding Hyperlinks:</strong> Highlight text, click the chain-link icon (&infin;), paste your web address (e.g. <code>https://mofa.gov.bd</code>), and press Enter.</li>
    <li><strong>Saving Your Changes:</strong> Click the blue <strong>Update</strong> button at the top right.</li>
  </ul>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 3: MEDIA & PDF DOCUMENTS                                -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 3</div>
  <h2>Managing Media &amp; PDF Documents</h2>

  <h3>3.1 The Media Library</h3>
  <p>The <strong>Media Library</strong> holds all photographs, official documents, PDFs, logos, and emblems.</p>

  <div class="figure-card">
    <img src="{images['12_media']}" alt="Media Library">
    <div class="figure-caption">Figure 3.1: WordPress Media Library showing uploaded images and documents</div>
  </div>

  <h3>3.2 Uploading Files (PDFs &amp; Photos)</h3>
  <ol class="steps">
    <li>In the left sidebar, click <strong>Media</strong> &rarr; <strong>Add Media File</strong>.</li>
    <li>Drag files from your computer folder directly onto the dashed box, or click <strong>Select Files</strong>.</li>
    <li>The upload completes automatically in a few seconds.</li>
  </ol>

  <div class="avoid-break">
    <h3>3.3 Creating a "Download PDF" Link or Button</h3>
    <ol class="steps">
      <li>Go to <strong>Media</strong> &rarr; <strong>Library</strong> and click on your uploaded PDF.</li>
      <li>In the popup window, find the <strong>File URL</strong> field on the right and click <strong>Copy URL to clipboard</strong>.</li>
      <li>Open the page or notice where you want the link (e.g. the <em>Constitution</em> page).</li>
      <li>Type text like <em>"Click here to download the AOFA Constitution (PDF)"</em>, highlight it, click the <strong>Link icon</strong>, and paste the URL.</li>
      <li>Click <strong>Update</strong> to save the page.</li>
    </ol>

    <div class="callout callout-tip">
      <strong>Image Best Practices:</strong> Keep photograph file sizes between <strong>200 KB and 800 KB</strong>. Avoid uploading raw 15 MB photos straight from professional cameras, as this slows down page loading on smartphones.
    </div>
  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 4: MANAGING CUSTOM AOFA CONTENT                          -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 4.1</div>
  <h2>Members Directory (Ambassadors)</h2>
  <p>The AOFA website maintains a database of former BCS(FA) career ambassadors, divided into <strong>Regular</strong> and <strong>Honorary</strong> members.</p>

  <div class="figure-card">
    <img src="{images['05_members']}" alt="Members List">
    <div class="figure-caption">Figure 4.1: Member Directory List with Member Types (Regular / Honorary)</div>
  </div>

  <h3>How to Add a New Ambassador</h3>
  <ol class="steps">
    <li>Click <strong>Members</strong> &rarr; <strong>Add New Member</strong> in the left sidebar.</li>
    <li><strong>Ambassador Name:</strong> Enter their full diplomatic title and name in the top box (e.g. <code>Amb. Dr. Muhammad Azizur Rahman</code>).</li>
    <li>Scroll down to the <strong>Member Details</strong> box:</li>
  </ol>

  <div class="figure-card">
    <img src="{images['06_member_meta']}" alt="Member Details Box">
    <div class="figure-caption">Figure 4.2: Dedicated Member Details Form (Member No, Address, Phone, Email)</div>
  </div>

  <ol class="steps" start="4">
    <li>Fill in the fields:
      <ul class="bullets">
        <li><strong>Member No / Serial:</strong> Official membership registration serial number.</li>
        <li><strong>Address:</strong> Residential or mailing address in Dhaka / overseas.</li>
        <li><strong>Phone:</strong> Primary telephone / mobile contact number.</li>
        <li><strong>Email:</strong> Official or personal email address.</li>
      </ul>
    </li>
    <li><strong>Member Type:</strong> In the right sidebar panel, check either <span class="badge badge-gold">regular</span> or <span class="badge">honorary</span>.</li>
    <li><strong>Portrait Photo:</strong> In the right sidebar, click <strong>Set featured image</strong> and select their photo.</li>
    <li>Click the blue <strong>Publish</strong> button at the top right.</li>
  </ol>
</div>

<div class="page-break">
  <div class="section-tag">Chapter 4.2</div>
  <h2>Executive Committee Management</h2>
  <p>The Executive Committee (EC) roster is organized into biennial governance terms (e.g. 2022–2023, 2024–2025, 2026–2027).</p>

  <div class="figure-card">
    <img src="{images['07_ec']}" alt="Executive Committee List">
    <div class="figure-caption">Figure 4.3: Executive Committee Management Roster by Committee Term</div>
  </div>

  <h3>Adding an Executive Committee Member</h3>
  <ol class="steps">
    <li>Click <strong>Executive Committee</strong> &rarr; <strong>Add New EC Member</strong> in the left sidebar.</li>
    <li><strong>Title Box:</strong> Enter the name and title (e.g. <code>Amb. Jahangir Saadat – Vice President-I</code>).</li>
    <li><strong>Executive Committee Details Box:</strong>
      <ul class="bullets">
        <li><strong>Serial No:</strong> Enter <code>1</code> for President, <code>2</code> for Vice President-I, <code>3</code> for Vice President-II, <code>4</code> for Secretary General, <code>5</code> for Treasurer, etc. This enforces proper diplomatic protocol order.</li>
        <li><strong>Position:</strong> Type the portfolio title (e.g. <code>President</code> or <code>Secretary General</code>).</li>
        <li><strong>Term:</strong> Enter the term period (e.g. <code>2026-2027</code>).</li>
      </ul>
    </li>
    <li><strong>Committee Terms (Right Sidebar):</strong> Check the box corresponding to their term (e.g. <strong>2026-2027</strong>).</li>
    <li><strong>Photo:</strong> Click <strong>Set featured image</strong> to attach their official portrait.</li>
    <li>Click <strong>Publish</strong>.</li>
  </ol>
</div>

<div class="page-break">
  <div class="section-tag">Chapter 4.3 &amp; 4.4</div>
  <h2>Notices, Articles &amp; Book Reviews</h2>

  <h3>4.3 Official Notices &amp; Circulars</h3>
  <p>Used for publishing AGM circulars, election schedules, picnics, and official announcements.</p>

  <div class="figure-card">
    <img src="{images['08_notice']}" alt="Notices Editor">
    <div class="figure-caption">Figure 4.4: Notice Publishing Interface</div>
  </div>

  <ol class="steps">
    <li>Click <strong>Notices</strong> &rarr; <strong>Add New Notice</strong>.</li>
    <li>Enter the headline (e.g. <em>"Notice of 12th Annual General Meeting (AGM) 2026"</em>).</li>
    <li>Paste the announcement body text in the content editor.</li>
    <li>If there is a signed physical circular, attach the PDF download link (see Chapter 3.3).</li>
    <li>Click <strong>Publish</strong>. The notice immediately appears on the website homepage and notice archive.</li>
  </ol>

  <h3>4.4 Articles &amp; Book Reviews</h3>
  <p>Allows publishing scholarly articles, diplomatic memoirs, and book reviews authored by members.</p>

  <div class="figure-card">
    <img src="{images['09_article']}" alt="Article Details Box">
    <div class="figure-caption">Figure 4.5: Article Details Form with Author, Publication Citation &amp; Language Selector</div>
  </div>

  <ol class="steps">
    <li>Click <strong>Articles &amp; Books</strong> &rarr; <strong>Add New Article</strong>.</li>
    <li>Fill the fields in the <strong>Article Details</strong> box:
      <ul class="bullets">
        <li><strong>Author / Reviewer:</strong> e.g. <code>Amb. M. Humayun Kabir</code></li>
        <li><strong>Originally Published In:</strong> e.g. <code>The Daily Star</code></li>
        <li><strong>Language:</strong> Choose <strong>English</strong> or <strong>Bengali</strong> from the dropdown.</li>
      </ul>
    </li>
    <li>Under <strong>Article Categories</strong> (right sidebar), check <strong>Article</strong> or <strong>Book Review</strong>.</li>
    <li>Attach book cover or portrait via <strong>Set featured image</strong> and click <strong>Publish</strong>.</li>
  </ol>
</div>

<div class="page-break">
  <div class="section-tag">Chapter 4.5</div>
  <h2>Photo Gallery &amp; Event Albums</h2>
  <p>Showcases diplomatic galas, delegations, picnics, and general assemblies using a high-speed multi-photo batch uploader.</p>

  <div class="figure-card">
    <img src="{images['10_gallery']}" alt="Photo Gallery Multi-Upload">
    <div class="figure-caption">Figure 4.6: One-Click Multiple Photo Upload System for Event Albums</div>
  </div>

  <h3>How to Upload an Event Album</h3>
  <ol class="steps">
    <li>Click <strong>Photo Gallery</strong> &rarr; <strong>Add New Photo</strong>.</li>
    <li>Enter the Album Event Title (e.g. <em>"AOFA Diplomatic Gala Dinner 2026"</em>).</li>
    <li>In the <strong>Gallery Album Photos</strong> box, click the blue button: <strong>Select / Upload Multiple Photos</strong>.</li>
    <li>In the media window, select multiple photos simultaneously (hold <code>Ctrl</code> on Windows or <code>Cmd</code> on Mac) and click <strong>Select</strong>.</li>
    <li>Thumbnails of all chosen pictures appear in the album grid instantly. Click the small red (&times;) on any photo to remove it.</li>
    <li>Click <strong>Set featured image</strong> in the right sidebar to designate the main album cover.</li>
    <li>Click <strong>Publish</strong>. The album is now live on the website.</li>
  </ol>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 5: NAVIGATION MENUS                                      -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 5</div>
  <h2>Managing the Navigation Menu</h2>
  <p>The navigation menu across the top of the website directs visitors to all key sections.</p>

  <div class="figure-card">
    <img src="{images['11_menu']}" alt="Menu Editor">
    <div class="figure-caption">Figure 5.1: Navigation Menu Builder with Drag-and-Drop Sub-items</div>
  </div>

  <h3>5.1 Opening the Menu Editor</h3>
  <p>Go to <strong>Appearance</strong> &rarr; <strong>Menus</strong> in the left sidebar. Ensure <strong>Primary Menu</strong> is selected.</p>

  <h3>5.2 Adding Links to the Navigation Bar</h3>
  <ol class="steps">
    <li>Under <strong>Add menu items</strong> on the left, click <strong>Pages</strong> &rarr; <strong>View All</strong>.</li>
    <li>Check the box next to any page you want to show and click <strong>Add to Menu</strong>.</li>
    <li>The new link card will appear at the bottom of the right-hand structure.</li>
  </ol>

  <h3>5.3 Reordering &amp; Creating Dropdown Sub-menus</h3>
  <ul class="bullets">
    <li><strong>To Reorder:</strong> Click and drag any menu item card up or down.</li>
    <li><strong>To Create a Dropdown Sub-menu:</strong> Drag an item underneath another and slide it slightly to the right until it indents. It will display a <span class="badge badge-sub">sub item</span> badge.</li>
  </ul>

  <div class="callout callout-warning">
    <strong>Always Save:</strong> Click the blue <strong>Save Menu</strong> button at the bottom right. Menu modifications are not saved automatically.
  </div>
</div>

<!-- ═════════════════════════════════════════════════════════════════ -->
<!-- CHAPTER 6 & 7: SECURITY & TROUBLESHOOTING                       -->
<!-- ═════════════════════════════════════════════════════════════════ -->
<div class="page-break">
  <div class="section-tag">Chapter 6 &amp; 7</div>
  <h2>Security, User Roles &amp; Troubleshooting</h2>

  <h3>6.1 User Roles &amp; Safe Delegation</h3>
  <p>When giving website access to new office staff (<strong>Users</strong> &rarr; <strong>Add New User</strong>):</p>
  <ul class="bullets">
    <li><strong>Editor (Recommended for staff):</strong> Can create, edit, and publish pages, members, notices, and galleries. Cannot break plugins, themes, or core settings.</li>
    <li><strong>Administrator:</strong> Full technical control. Reserve strictly for primary system managers.</li>
  </ul>

  <h3>7.1 Frequently Asked Questions &amp; Common Fixes</h3>

  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 45%;">Problem / Question</th>
        <th style="width: 55%;">Solution</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>I updated a page, but the old text still appears on the live site!</strong></td>
        <td>Your browser has cached the old page. Press <strong>Ctrl + F5</strong> (Windows) or <strong>Cmd + Shift + R</strong> (Mac) to force-refresh the latest version.</td>
      </tr>
      <tr>
        <td><strong>How do I temporarily unpublish something without deleting it?</strong></td>
        <td>Open the item, look in the right sidebar under <strong>Status</strong>, change it from <em>Published</em> to <strong>Draft</strong>, and click <strong>Save Draft</strong>.</td>
      </tr>
      <tr>
        <td><strong>I accidentally deleted a member or notice. Can I recover it?</strong></td>
        <td>Yes! Go to the section list, click the <strong>Trash</strong> link at the top, hover over the item, and click <strong>Restore</strong> (kept for 30 days).</td>
      </tr>
      <tr>
        <td><strong>Can I see my changes before making them public?</strong></td>
        <td>Yes. While in the editor, click the <strong>Preview</strong> button in the top toolbar to view the page exactly as visitors will see it.</td>
      </tr>
    </tbody>
  </table>

  <div class="callout callout-info" style="margin-top: 30px;">
    <strong>Emergency Technical Support:</strong><br>
    For hosting renewals, domain configurations, or automated server backups, contact the designated AOFA Technical Webmaster or hosting provider.
  </div>

  <div style="text-align: center; margin-top: 40px; font-size: 8.5pt; color: #94A3B8;">
    &mdash; END OF MANUAL &mdash;<br>
    Association of Former BCS(FA) Ambassadors (AOFA) Web Administration System
  </div>
</div>

</body>
</html>
"""

    print("Writing print-optimized HTML to:", OUTPUT_HTML)
    with open(OUTPUT_HTML, "w", encoding="utf-8") as f:
        f.write(html_content)

    print("Generating PDF via Headless Google Chrome...")
    cmd = [
        "google-chrome",
        "--headless=new",
        "--no-sandbox",
        "--disable-gpu",
        "--no-pdf-header-footer",
        f"--print-to-pdf={OUTPUT_PDF}",
        "--virtual-time-budget=4000",
        OUTPUT_HTML
    ]
    res = subprocess.run(cmd, capture_output=True, text=True)
    if res.returncode != 0:
        print("Chrome error:", res.stderr)
        sys.exit(1)

    print("Copying PDF to root and artifact directories...")
    subprocess.run(["cp", OUTPUT_PDF, ROOT_PDF], check=True)
    subprocess.run(["cp", OUTPUT_PDF, ARTIFACT_PDF], check=True)

    size = os.path.getsize(OUTPUT_PDF)
    print(f"SUCCESS: Generated PDF ({size} bytes) at:")
    print(f"  1. {OUTPUT_PDF}")
    print(f"  2. {ROOT_PDF}")
    print(f"  3. {ARTIFACT_PDF}")

if __name__ == "__main__":
    main()
