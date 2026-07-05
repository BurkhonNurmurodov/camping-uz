import os

files = [
    '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz/assets/css/style.css',
    '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz/assets/css/site.css',
]

replacements = [
    # Dark greens / Inks to Dark Blues
    ('#1C231F', '#08101a'),
    ('#1c231f', '#08101a'),
    ('#1D231F', '#08101a'),
    ('#1d231f', '#08101a'),
    ('#101311', '#04080d'),
    ('#272C28', '#161f2c'),
    ('#272c28', '#161f2c'),
    
    # Light backgrounds with green tints to blue tints
    ('#F9F9F7', '#f5f8fb'),
    ('#f9f9f7', '#f5f8fb'),
    ('#B1B6B3', '#b0c0ce'),
    ('#b1b6b3', '#b0c0ce'),
    
    # RGBA values
    # Primary Green -> Dark Blue (13, 36, 68)
    ('99, 171, 69', '13, 36, 68'),
    
    # Deep Green -> Deep Blue (6, 21, 42)
    ('11, 61, 44', '6, 21, 42'),
    
    # Orange/Sand -> Gold (187, 145, 87)
    ('247, 146, 30', '187, 145, 87'),
    
    # Ink 1 (28, 35, 31) -> Dark Blue (8, 16, 26)
    ('28, 35, 31', '8, 16, 26'),
    
    # Ink 2 (29, 35, 31) -> Dark Blue (8, 16, 26)
    ('29, 35, 31', '8, 16, 26'),
    
    # Darkest Ink (16, 19, 17) -> Darkest Blue (4, 8, 13)
    ('16, 19, 17', '4, 8, 13'),
    
    # Dark Gray-Green (39, 44, 40) -> Dark Gray-Blue (22, 31, 44)
    ('39, 44, 40', '22, 31, 44'),
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
