## Projet Symfony 6.4.* de base

Version : **1.0.0**

### Fonctionalités

- Tablettes
- Utilisateurs
   - Inscription (captcha)
   - Authentification
   - Mot de passe oublié
- Logs
   - Formulaire de recherche
- EasyAdmin
- Formulaires
   - CK Editor
   - DateRangePicker
   - Input Mask
   - DualListBox
   - Live Component Twig de recherche
- Datatables
- FullCalendar
- UX Charts
- Pivottable
- Makefile
- PDF
   - Signets
   - Fichiers attachés
   - Rotation texte et image
   - Géométrie
   - Etiquettes
   - AutoPrint
   - Graphique camembert et histogramme
   - Codes à barres
### Installation
```bash
git clone https://github.com/rcnchris/sf64-base.git my-project-dir
cd my-project-dir
composer app-install
php bin/console app:env-install
git commit -m "Création projet"
code .
```