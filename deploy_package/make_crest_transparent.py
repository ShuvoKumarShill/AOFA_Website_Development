#!/usr/bin/env python3
from PIL import Image, ImageDraw, ImageFilter
import numpy as np

src_path = '/home/shuvo/github/AOFA_Website_Development/wp-content/uploads/aofa-crest.png'
dst_path = '/home/shuvo/github/AOFA_Website_Development/wp-content/uploads/aofa-crest-no-black.png'

img = Image.open(src_path).convert('RGBA')
w, h = img.size
center_x, center_y = w / 2.0, h / 2.0

# Convert image to numpy array
arr = np.array(img, dtype=np.float32)
r, g, b, a = arr[:,:,0], arr[:,:,1], arr[:,:,2], arr[:,:,3]

# Create coordinate grid
y, x = np.ogrid[:h, :w]
dist = np.sqrt((x - center_x)**2 + (y - center_y)**2)

# Outer circle radius of the emblem is around 397-398 pixels
radius_outer = 398.0
feather = 3.0 # smooth transition width in pixels

# Create smooth circular alpha mask
alpha_circle = np.clip((radius_outer + feather - dist) / (feather * 2.0), 0.0, 1.0)

# Also remove dark background colors (where luminance < 50 or dark blue/black) outside radius 380
luminance = 0.299 * r + 0.587 * g + 0.114 * b
# Dark background is dark blue/navy (R<35, G<45, B<70)
dark_bg_mask = (r < 40) & (g < 50) & (b < 75) & (dist > 375)

# Alpha calculation
new_alpha = a * alpha_circle
# For dark background pixels near the edge, fade alpha out smoothly
new_alpha[dark_bg_mask] = 0.0

# Set RGBA array
arr[:, :, 3] = np.clip(new_alpha, 0, 255)

result_img = Image.fromarray(arr.astype(np.uint8), mode='RGBA')

# Save transparent PNG
result_img.save(dst_path, format='PNG')
print(f"Saved transparent crest image to {dst_path}")

# Also overwrite aofa-crest.png with transparent version or keep both
result_img.save('/home/shuvo/github/AOFA_Website_Development/wp-content/uploads/aofa-crest.png', format='PNG')
result_img.save('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/uploads/aofa-crest-no-black.png', format='PNG')
result_img.save('/home/shuvo/github/AOFA_Website_Development/aofa_site_ready/public_html/wp-content/uploads/aofa-crest.png', format='PNG')

print("Overwritten local crest files successfully.")
