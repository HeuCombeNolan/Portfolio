import csv
import os
import plotly.graph_objs as go #commande pour installer l'extension: pip install plotly

# Chemin vers le fichier CSV
csv_file_path = os.path.join(os.path.expanduser('~'), 'Desktop', 'sae15', 'fichier_borne.csv')

# Lecture du fichier CSV
with open(csv_file_path, newline='', encoding='utf-8') as csvfile:
    data = list(csv.reader(csvfile, delimiter=';'))

# Retirer les en-têtes
headers = data[0]
data = data[1:]

# Index des colonnes à exclure
columns_to_exclude = ['Source', 'Code Officiel Région', 'Code EPCI', 'Nom de l\'EPCI']
excluded_columns_indices = [headers.index(col) for col in columns_to_exclude if col in headers]

# Créer un dictionnaire pour les bornes par commune et compter leur nombre
bornes_par_commune = {}
for row in data:
    commune = row[headers.index('Commune')]
    if commune not in bornes_par_commune:
        bornes_par_commune[commune] = []
    bornes_par_commune[commune].append(row)

# Générer les liens pour chaque commune et afficher le nombre de bornes
html_content = "<html><head><title>Bornes par commune</title>"
html_content += "<style>table, th, td { border: 1px solid black; border-collapse: collapse; padding: 5px; }</style>"
html_content += "</head><body>"
html_content += "<h1>Bornes par commune</h1>"
html_content += "<input type='text' id='searchInput' onkeyup='searchCommune()' placeholder='Rechercher une commune...'>"
html_content += "<ul id='communesList'>"
for commune, bornes in bornes_par_commune.items():
    html_content += f"<li><a href='javascript:void(0)' onclick='toggleDetails(\"{commune}\")'>{commune} - {len(bornes)} borne(s)</a></li>"
    html_content += f"<div id='{commune}_details' style='display: none;'>"
    html_content += "<table>"
    # En-têtes
    html_content += "<tr>"
    for idx, header in enumerate(headers):
        if idx not in excluded_columns_indices:
            html_content += f"<th>{header}</th>"
    html_content += "</tr>"
    # Lignes de données
    for row in bornes:
        html_content += "<tr>"
        for idx, value in enumerate(row):
            if idx not in excluded_columns_indices:
                html_content += f"<td>{value}</td>"
        html_content += "</tr>"
    html_content += "</table>"
    html_content += "</div>"
html_content += "</ul>"

html_content += "<script>"
html_content += "function searchCommune() {"
html_content += "  var input, filter, ul, li, a, i, txtValue;"
html_content += "  input = document.getElementById('searchInput');"
html_content += "  filter = input.value.toUpperCase();"
html_content += "  ul = document.getElementById('communesList');"
html_content += "  li = ul.getElementsByTagName('li');"
html_content += "  for (i = 0; i < li.length; i++) {"
html_content += "    a = li[i].getElementsByTagName('a')[0];"
html_content += "    txtValue = a.textContent || a.innerText;"
html_content += "    if (txtValue.toUpperCase().indexOf(filter) > -1) {"
html_content += "      li[i].style.display = '';"
html_content += "    } else {"
html_content += "      li[i].style.display = 'none';"
html_content += "    }"
html_content += "  }"
html_content += "}"
html_content += "function toggleDetails(commune) {"
html_content += "  var details = document.getElementById(commune + '_details');"
html_content += "  if (details.style.display === 'none') {"
html_content += "    details.style.display = 'block';"
html_content += "  } else {"
html_content += "    details.style.display = 'none';"
html_content += "  }"
html_content += "}"
html_content += "</script>"
html_content += "</body></html>"

# Écrire le contenu dans un fichier HTML
with open('output.html', 'w', encoding='utf-8') as html_file:
    html_file.write(html_content)

print("Page HTML générée avec succès dans le fichier 'output.html'")

