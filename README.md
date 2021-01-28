## COME FAR PARTIRE IN LOCALE

-   Creare sul proprio computer una folder structure di questo tipo
    -   instudy (sarebbe la root)
        -   projects
        -   projectsRepo
        -   res
            -   projectIcons
-   Fare il git clone della repo dentro la root
-   rinominare instudy-cms in cms
-   composer install
-   (Il resto delle cartelle serve solo a simulare quella che sarà la folder structure dell'app sul server; se si ha già la versione locale dell'app instudy allora tanto vale usare quella e mettere solo la cartella cms)
-   Assicurarsi di avere un server MySQL in ascolto sulla porta 3306 (es. con XAMPP su Windows)
-   Creare un database di nome hj2jbnva_lilly.instudy e buttarci i dati dell'app (se si vuole usare un nome diverso basta cambiarlo nel file ENV)
-   (I dati del DB dovrebbero essere quelli con la struttura aggiornata)
-   Creare un secondo database di nome instudy_cms (anche questo nome può essere cambiato)
-   php artisan migrate
-   php artisan db:seed
-   installare gli script con npm install && npm run dev (a volte ti chiede di ripetere il comando)
-   nella cartella cms, far partire php artisan serve

## DOVE POSSO ESEGUIRE LE MODIFICHE?

Gli unici file degni di note apparte l'env, sono

-   UploadFileController.php: qui è dove andrebbero fatte le modifiche ai file zip (es. manipolare i file)
    -   cerca il metodo modifyFiles, le modifiche le puoi fare la
-   cmsUploadFileController.php: file dove vengono presi i dati da mostrare in pagina
-   web.php (il router)
-   le views, in particolare cmsUploadStudio, action e success

## PROBLEMI

-   Va molto lento quando si fa partire una richiesta

## REACT

Non è attivato perchè ancora non serve. Per attivarlo bisognerebbe decommentare l'elemento root nella pagina

## NOTE SUI 2 DATABASE

Per evitare di dover rifare i modelli avendo già un DB, non è stato usato Eloquent ma SQL raw.

per evitare conflitti ho preferito separare i dati in due database:

-   mysql2: quello in cui stanno tutti i dati dell'app
-   mysql: quello in cui metto i dati di autenticazione

I parametri di configurazione sono nel file env (tra le due connessioni cambia solo il DB)

## SVUOTARE CACHE

Se capitano problemi dopo qualche cambio di impostazioni, probabilmente va lanciato questo comando

php artisan config:clear

## INSTALLAZIONE SU SHARED HOSTING APACHE

1. Istruzioni base per installazione
   https://stackoverflow.com/questions/41407758/how-to-install-laravel-app-in-subfolder-of-shared-host
2. modificare il file .ENV per mettere i dati giusti (avendo due database come descritto prima)
3. Proteggere tutti i dati sempre tramite .htaccess
   https://gist.github.com/shakee93/7222b7f2429b467731211cd0dce35410

## MAIL DI RESET

Ho preso le funzionalità dello script di Sasha ma le ho implementate con il Mailer di Laravel

**Per il futuro ricordarsi di circondare i parametri nel file ENV con "" se contengono il carattere #**
