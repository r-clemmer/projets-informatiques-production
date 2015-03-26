INSTRUCTIONS PREALABLE A L INSTALLATION
* activer le module rewrite d'apache

INSTRUCTIONS D INSTALLATION DU PROJET
* copier les sources du projet dans le répertoire
* créer un alias dirigeant vers le répertoire du projet /public avec la ligne Override All
* modifier dans le fichier .htaccess situé dans le répertoire /public la ligne "RewriteBase /PMQVisionV4" par "RewriteBase /aliasprécédementchoisi"

INSTRUCTIONS D INSTALLATION DE LA BASE DE DONNEES
* créer la structure de la base de données grâce au fichier pmqvision4.sql situé dans le répertoire /docs du projet
* faire un import des données grâce au fichier pmqvision4.sql.zip

INSTRUCTIONS DE RECUPERATION DES DONNEES DE LA V3 A LA V4
* le script est fourni dans l'archive moulinette.rar
*!!ATTENTION!!*
*!!avant de lancer le script, vérifier les informations d'accès aux bases de données dans les fichiers "connect_base1.php" et "connect_base2.php"
*!!la structure de la base de données de la V4 doit être créée avant

CONFIGURATION DU PROJET
* le fichier de configuration du projet se situe dans le répertoire du projet /application/configs/application.ini
* préciser les informations de la connexion à la base de données situées après la ligne ";initialize database"
* "resources.db.params.host" : adresse de l'hôte
* "resources.db.params.username" : login
* "resources.db.params.password" : mot de passe
* "resources.db.params.dbname" : nom de la base de données
* préciser les informations concernant l envoi de mails après la ligne "; initialize emails"
* "mails.smtp" : adresse du serveur SMTP
* "mails.from" : adresse mail de l'expéditeur
* "mails.default" : adresse(s) mail par défault pour la réception de notifications ( si plusieurs adresses, les séparer par le caractère ";" )

INSTRUCTIONS DE RECUPERATION DES FICHIERS CONTACTS/CANDIDATS
* instructions fournies dans le fichier "recup documents v3-v4.doc"