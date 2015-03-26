INSTRUCTIONS PREALABLE A L INSTALLATION
* activer le module rewrite d'apache

INSTRUCTIONS D INSTALLATION DU PROJET
* copier les sources du projet dans le r�pertoire
* cr�er un alias dirigeant vers le r�pertoire du projet /public avec la ligne Override All
* modifier dans le fichier .htaccess situ� dans le r�pertoire /public la ligne "RewriteBase /PMQVisionV4" par "RewriteBase /aliaspr�c�dementchoisi"

INSTRUCTIONS D INSTALLATION DE LA BASE DE DONNEES
* cr�er la structure de la base de donn�es gr�ce au fichier pmqvision4.sql situ� dans le r�pertoire /docs du projet
* faire un import des donn�es gr�ce au fichier pmqvision4.sql.zip

INSTRUCTIONS DE RECUPERATION DES DONNEES DE LA V3 A LA V4
* le script est fourni dans l'archive moulinette.rar
*!!ATTENTION!!*
*!!avant de lancer le script, v�rifier les informations d'acc�s aux bases de donn�es dans les fichiers "connect_base1.php" et "connect_base2.php"
*!!la structure de la base de donn�es de la V4 doit �tre cr��e avant

CONFIGURATION DU PROJET
* le fichier de configuration du projet se situe dans le r�pertoire du projet /application/configs/application.ini
* pr�ciser les informations de la connexion � la base de donn�es situ�es apr�s la ligne ";initialize database"
* "resources.db.params.host" : adresse de l'h�te
* "resources.db.params.username" : login
* "resources.db.params.password" : mot de passe
* "resources.db.params.dbname" : nom de la base de donn�es
* pr�ciser les informations concernant l envoi de mails apr�s la ligne "; initialize emails"
* "mails.smtp" : adresse du serveur SMTP
* "mails.from" : adresse mail de l'exp�diteur
* "mails.default" : adresse(s) mail par d�fault pour la r�ception de notifications ( si plusieurs adresses, les s�parer par le caract�re ";" )

INSTRUCTIONS DE RECUPERATION DES FICHIERS CONTACTS/CANDIDATS
* instructions fournies dans le fichier "recup documents v3-v4.doc"