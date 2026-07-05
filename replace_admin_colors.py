import os

filepath = '/Users/burkhonnurmurodov/Downloads/gowilds pack/camping-uz-admin/assets/css/admin-extra.css'

if os.path.exists(filepath):
    with open(filepath, 'r') as f:
        content = f.read()
    
    content = content.replace('#1c231f', '#08101a')
    content = content.replace('#1C231F', '#08101a')
        
    with open(filepath, 'w') as f:
        f.write(content)
    print(f"Updated {filepath}")
