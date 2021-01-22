## COME FAR PARTIRE

-   Creare sul proprio computer una folder structure di questo tipo
    instudy
    ├── cms
    ├── projects
    ├── projectsRepo
    └── res
    .....└── projectIcons
-   Fare il git clone della repo dentro la cartella cms
-   (Il resto delle cartelle serve solo a simulare quella che sarà la folder structure dell'app sul server; se si ha già la versione locale dell'app instudy allora tanto vale usare quella e mettere solo la cartella cms)
-   Assicurarsi di avere un server MySQL in ascolto sulla porta 3306 (es. con XAMPP su Windows)
-   Creare un database di nome hj2jbnva_lilly.instudy e buttarci i dati dell'app (se si vuole usare un nome diverso basta cambiarlo nel file ENV)
-   Creare un secondo database di nome instudy_cms (anche questo nome può essere cambiato)
-   php artisan migrate
-   php artisan db:seed
-   nella cartella cms, far partire php artisan serve

## DOVE POSSO ESEGUIRE LE MODIFICHE?

Gli unici file degni di note apparte l'env, sono

-   UploadFileController.php: qui è dove andrebbero fatte le modifiche ai file zip (es. manipolare i file)
    -   cerca il metodo modifyFiles, le modifiche le puoi fare la
-   cmsUploadFileController.php: file dove vengono presi i dati da mostrare in pagina
-   web.php (il router)
-   le views, in particolare cmsUploadStudio, action e success

## PROBLEMI

-   Per qualche motivo va MOLTO lento (ci mette secondi prima di completare la richiesta), forse però è solo il development server che fa così;
    spero che in production vada più veloce, altrimenti andrà trovato un fix
    -   Va ancora fatta un'impostazione per permettere all'app di funzionare in una sottocartella del server (cms)

## REACT

Non è attivato perchè ancora non serve. Per attivarlo bisognerebbe decommentare l'elemento root nella pagina

# NOTE SUI 2 DATABASE

Per via del fatto che c'era già un database, onde evitare di dover ricostruirlo nei modelli e per fare una cosa veloce
ho fatto le query a mano.

Ci sono due connessioni al database:

-   mysql2: quello in cui stanno tutti i dati dell'app
-   mysql: quello in cui metto i dati di autenticazione; ho preferito evitare di mischiare i due db onde evitare problemi

I parametri di configurazione sono nel file env (cambia solo il nome db: andrà cambiato prima di mettere in production)

## SVUOTARE CACHE

Se capitano problemi inspiegabili dopo qualche cambio di impostazioni, probabilmente va fatto questo

php artisan config:clear
