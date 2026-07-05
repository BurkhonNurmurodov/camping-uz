import os

filepath = '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz/assets/css/site.css'

replacements = [
    ('11,61,44', '6,21,42'),
    ('28,35,31', '8,16,26')
]

if os.path.exists(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
    
    for old_col, new_col in replacements:
        content = content.replace(old_col, new_col)
        
    with open(filepath, 'w') as f:
        f.write(content)
    print(f"Updated {filepath}")
