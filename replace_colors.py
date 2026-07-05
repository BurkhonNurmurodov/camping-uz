import os

files = [
    '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz/assets/css/style.css',
    '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz/assets/css/site.css',
]

replacements = [
    ('#63AB45', '#0d2444'),
    ('#63ab45', '#0d2444'),
    ('#0B3D2C', '#06152a'),
    ('#0b3d2c', '#06152a'),
    ('#F7921E', '#bb9157'),
    ('#f7921e', '#bb9157'),
]

for filepath in files:
    if os.path.exists(filepath):
        with open(filepath, 'r') as f:
            content = f.read()
        
        for old_col, new_col in replacements:
            content = content.replace(old_col, new_col)
            
        with open(filepath, 'w') as f:
            f.write(content)
        print(f"Updated {filepath}")
