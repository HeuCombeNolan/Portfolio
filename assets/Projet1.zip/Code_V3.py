#Ce script Python est conçu pour analyser et représenter visuellement les données extraites d'un fichier CSV. 
#Il commence par charger les informations, les organise ensuite par commune, puis génère une page HTML interactive. 
#Cette page web permet d'explorer les données des bornes de recharge par localité, fournissant ainsi une vision détaillée de chaque commune.

#En plus de cette organisation par commune, le script crée des diagrammes interactifs. 
#Ces graphiques permettent de visualiser la répartition des bornes par opérateur, type de prise, puissance délivrée et conditions d'accès. 
#Ces éléments visuels offrent une compréhension claire et facilitent l'analyse approfondie de l'infrastructure de recharge électrique dans la région des Hauts-de-France.

#Une fois toutes les données et graphiques générés, le script assemble le contenu dans une page HTML finale, prête à être consultée et explorée. 
#Cette page est ensuite enregistrée dans un fichier nommé `index.html` qui se trouve dans le home.

import csv #module pour lire le csv
import os #module pour acceder au fichier csv
import plotly.graph_objects as go #module pour les graphiques
from plotly.subplots import make_subplots

# Chemin vers le fichier CSV
csv_file_path = os.path.join(os.path.expanduser('~'), 'Desktop', 'sae15', 'fichier_borne.csv')

# Chemin vers le répertoire contenant votre fichier CSS
css_folder = 'css'
css_file = 'styles.css'

# Chemin absolu du fichier CSS
css_file_path = os.path.join(os.path.dirname(__file__), css_folder, css_file)

# Lecture du fichier CSV
with open(csv_file_path, newline='', encoding='utf-8') as csvfile:
    data = list(csv.reader(csvfile, delimiter=';'))

# Retirer les en-têtes
headers = data[0]
data = data[1:]

# Index des colonnes à exclure
columns_to_exclude = ['Source', 'Code Officiel Région', 'Code EPCI', 'Nom de l\'EPCI']
excluded_columns_indices = [headers.index(col) for col in columns_to_exclude if col in headers]

# Création d'un dictionnaire pour les bornes par commune et compter leur nombre
bornes_par_commune = {}
for row in data:
    commune = row[headers.index('Commune')]
    if commune not in bornes_par_commune:
        bornes_par_commune[commune] = []
    bornes_par_commune[commune].append(row)

# Code html
html_content = "<html><head><title>Bornes électrique des Hauts de France</title>"
html_content += f"<link rel='stylesheet' href='css/styles.css'>"
html_content += "</head><body>"
html_content += "<h1>Bornes pour voitures électriques dans les Hauts-de-France</h1>"
html_content += "<img src='image.png' alt='' style=''>"
html_content += "<h2>Bienvenue dans une exploration passionnante des données des bornes de recharge dans les Hauts-de-France. Ces précieuses informations,soigneusement collectées et organisées par commune, dévoilent l'empreinte de l'électromobilité dans cette région dynamique. Ce site présente également des graphiques interactifs illustrant la répartition des bornes par opérateur, type de prise, puissance délivrée, et conditions d'accès. Ces éléments visuels fournissent une perspective claire de l'infrastructure de recharge, facilitant ainsi l'analyse et les recommandations pour son amélioration.</h2>"
html_content += "<input type='text' id='searchInput' onkeyup='searchCommune()' placeholder='Rechercher une commune...'>"
html_content += "<ul id='communesList'>"
for commune, bornes in bornes_par_commune.items():
    html_content += f"<li><a href='javascript:void(0)' onclick='toggleDetails(\"{commune}\")'>{commune} - {len(bornes)} borne(s)</a></li>"
    html_content += f"<div id='{commune}_details' style='display: none;'>"
    html_content += f"<table class='table-container'>"
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


# Index de la colonne 'Aménageur'
amenageur_index = next((i for i, header in enumerate(headers) if header == '\ufeffAménageur'), -1) #\ufeffAménageur car il y a un caractère spécial encodage avec UTF-8

# Aménageur
amenageurs_counts = {}
for row in data:
    amenageur = row[amenageur_index]
    amenageurs_counts[amenageur] = amenageurs_counts.get(amenageur, 0) + 1

# Filtrer les aménageur pour garder ceux au-dessus de 20 bornes
principaux_amenageurs = {amenageur: count for amenageur, count in amenageurs_counts.items() if count > 20}

prise_mapping = {
    'T2': 'Type 2 & prise domestique',
    'EF - T2': 'Type 2 & prise domestique',
    'E/F-T2': 'Type 2 & prise domestique',
    'T2 – E/F': 'Type 2 & prise domestique',
    'EF, T2': 'Type 2 & prise domestique',
    'EF-T2': 'Type 2 & prise domestique',
    'T2 - E/F': 'Type 2 & prise domestique',
    'T2p': 'Type 2 & prise domestique',
    'COMBO': 'CHAdeMo combo',
    'CHAdeMO, Combo': 'CHAdeMo combo',
    'CHAdeMo, Combo': 'CHAdeMo combo',
    'Chademo-CCS': 'CHAdeMo combo',
    'COMBO 2': 'CHAdeMO combo 2',
    'CHAdeMo, Combo2': 'CHAdeMO combo 2',
    'CHADEMO': 'CHAdeMo classique',
    'CCS350-CCS350-CCS350-CCS350-CCS350-CHAdeMO - T2': 'CHAdeMo classique'
}

# opérateur
operateur_index = headers.index('Opérateur')
operateurs_counts = {}
for row in data:
    operateur = row[operateur_index]
    operateurs_counts[operateur] = operateurs_counts.get(operateur, 0) + 1

# Diagramme circulaire pour les opérateurs
labels_op = list(operateurs_counts.keys())
sizes_op = list(operateurs_counts.values())

fig_operateurs = go.Figure(data=[go.Pie(labels=labels_op, values=sizes_op)])
fig_operateurs.update_layout(title='Diagramme circulaire pour les Opérateurs')

# Enseigne
enseigne_index = headers.index('Enseigne')
enseignes_counts = {}
for row in data:
    enseigne = row[enseigne_index]
    enseignes_counts[enseigne] = enseignes_counts.get(enseigne, 0) + 1

# Filtrer les enseignes pour ne garder que celles ayant plus de 20 borne sinon diagramme surcharger
principales_enseignes = {enseigne: count for enseigne, count in enseignes_counts.items() if count > 20}

# Diagramme circulaire pour les enseignes
labels_ens = list(principales_enseignes.keys())
sizes_ens = list(principales_enseignes.values())

fig_enseignes = go.Figure(data=[go.Pie(labels=labels_ens, values=sizes_ens)])
fig_enseignes.update_layout(title='Diagramme circulaire pour les Enseignes')

# Puissance délivrée
puissance_index = headers.index('Puissance délivrée')
puissances_counts = {}
for row in data:
    puissance = row[puissance_index]
    if puissance:  # Vérifier si la valeur n'est pas vide
        puissance = round(float(puissance.split('kW')[0]))  # Arrondir la valeur de la puissance délivrée
        puissances_counts[puissance] = puissances_counts.get(puissance, 0) + 1

# Diagramme circulaire pour la puissance délivrée
labels_puissance = list(puissances_counts.keys())
sizes_puissance = list(puissances_counts.values())

fig_puissance = go.Figure(data=[go.Pie(labels=labels_puissance, values=sizes_puissance)])
fig_puissance.update_layout(title='Diagramme circulaire pour la Puissance délivrée en kW')

#type de prise modifié selon le mapping
type_prise_index = headers.index('Type de prise')
types_prise_counts = {}

for row in data:
    type_prise = row[type_prise_index]
    type_prise_modifie = prise_mapping.get(type_prise, type_prise)
    types_prise_counts[type_prise_modifie] = types_prise_counts.get(type_prise_modifie, 0) + 1

# Diagramme circulaire pour les types de prise modifiés
labels_type_prise = list(types_prise_counts.keys())
sizes_type_prise = list(types_prise_counts.values())

fig_type_prise = go.Figure(data=[go.Pie(labels=labels_type_prise, values=sizes_type_prise)])
fig_type_prise.update_layout(title='Diagramme circulaire pour les Types de prise modifiés')

# condition d'accès
condition_acces_index = headers.index("Condition d'accès")
conditions_acces_counts = {}
for row in data:
    condition_acces = row[condition_acces_index].lower()  # Normalisation en minuscules
    conditions_acces_counts[condition_acces] = conditions_acces_counts.get(condition_acces, 0) + 1

# Fusionner les occurrences de 'payant' et 'Payant'
conditions_acces_counts['payant'] = conditions_acces_counts.get('payant', 0) + conditions_acces_counts.get('Payant', 0)
if 'Payant' in conditions_acces_counts:
    del conditions_acces_counts['Payant']

# Diagramme circulaire pour les conditions d'accès
labels_conditions = list(conditions_acces_counts.keys())
sizes_conditions = list(conditions_acces_counts.values())

fig_conditions_acces = go.Figure(data=[go.Pie(labels=labels_conditions, values=sizes_conditions)])
fig_conditions_acces.update_layout(title='Diagramme circulaire pour les Conditions d\'accès')

graph_html_op = fig_operateurs.to_html(full_html=False, include_plotlyjs='cdn')
graph_html_ens = fig_enseignes.to_html(full_html=False, include_plotlyjs='cdn')
graph_html_puissance = fig_puissance.to_html(full_html=False, include_plotlyjs='cdn')
graph_html_type_prise = fig_type_prise.to_html(full_html=False, include_plotlyjs='cdn')
graph_html_conditions_acces = fig_conditions_acces.to_html(full_html=False, include_plotlyjs='cdn')

# Pour ajoutez les graphiques à la fin du contenu HTML
html_content += f"<div>{graph_html_op}</div>"
html_content += f"<div>{graph_html_ens}</div>"
html_content += f"<div>{graph_html_puissance}</div>"
html_content += f"<div>{graph_html_type_prise}</div>"
html_content += f"<div>{graph_html_conditions_acces}</div>"
html_content += "<footer>Cette page a été réaliser par Nolan Heu--Combe pour la SAE 15</footer>"   

# Enregistrement du contenu dans un fichier HTML
with open('index.html', 'w', encoding='utf-8') as html_file:
    html_file.write(html_content)

print("Page HTML générée avec succès dans le fichier 'index.html'") #La page se trouve le home 