# Module de notation

## Installation

### Installation du module

1. Aller dans le dossier **prestashop/modules/**
2. ```$ git clone https://github.com/PierreClouet/module-prestashop.git noteestiam```
3. Se connecter au back office prestashop
4. Aller dans Modules > Modules & Services
5. Rechercher **"notation"**
6. Installer le module

### Configuration du front

1. Editez le fichier **prestashop/themes/non_du_theme/templates/catalog/product.tpl** en y ajoutant **{hook h='displayNotation'}** où vous souhaitez afficher le système de notation. Attention à ne pas le placer à l'intérieur d'un ```<form></form>```.

## Utilisation

### Côté front

Pour pouvoir noter un produit vous devez être conneté. Il n'est possible de noter qu'une seule fois un produit.

### Côté back

Vous pouvez donner un titre qui s'affichera en front. Vous avez égalemnet accès à la liste des notes et des commentaires. Vous avez la possibilité de supprimer une ou plusieurs notes. Chaque note est associée à un utilisateur et à un produit.