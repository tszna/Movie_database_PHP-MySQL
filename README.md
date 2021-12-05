# filmy_PHP
Jest to aplikacja wyświetlająca listę filmów, inspirowana tym co można znaleźć na stronie filmweb. Projekt został opracowany przy wykorzystaniu php, silnika szablonów twig, javascriptu oraz css. Baza danych opiera się na MySQL. Aplikacja pobiera z bazy danych tylko te filmy, które maja być wyświetlone na widoku. Pobieranie filmów odbywa się w sposób asynchroniczny, przy użyciu technologii Ajax.
<h4>Projekt posiada następujące moduły:</h4>
<ul>
<li>wyświetlanie listy filmów</li>
<li>rejestrację i logowanie</li>
<li>nadawanie uprawnień do wyświetlenia filtrów filmów</li>
<li>filtrowanie listy filmów według roku wydania, tytułu, reżysera, kategorii np. fantasy, dramat</li>

<img src="https://i.imgur.com/GU3YEaY.gif" alt="operation in app">

<li>filtr, który filtruje po tytule filmu, posiada dodatkowo moduł odpowiedzialny za wyświetlenie okna pod filtrem, z listą wszystkich pasujących filmów, po kliknięciu na jedną z pozycji w oknie, użytkownik przenosi się na stronę danego filmu.</li>
</ul>
<img src="https://i.imgur.com/SzDsNrG.gif" alt="operation in filtering module">

<h4>Instalacja projektu</h4>
Po pobraniu projektu należy umieścić go w katalogu o nazwie "filmy", a następnie uruchomić terminal w głównym katalogu aplikacji i wpisać w terminalu komendę:
<pre><code>composer install</code></pre>
Następnie w bazie mysql utworzyć bazę danych o nazwie: film, i użytkownika z uprawnieniami do tej bazy danych o loginie: film i haśle: film.
W kolejnym kroku należy zaimportować do stworzonej bazy danych plik sql, który znajduje się w głównym katalogu aplikacji.
Następnie uruchomić moduł PHP i MySQL np. w programie xampp. Strona główna aplikacji jest pod adresem: /filmy

<h4>Uruchomienia projektu poprzez docker</h4>
Po pobraniu projektu należy skopiować zawartość katalogu Docker i wkleić do głównego katalogu aplikacji, w taki sposób aby nadpisać istniejące pliki. Następnie należy uruchomić terminal w głównym katalogu aplikacji i wpisać komendę:
<pre><code>docker-compose up</code></pre>
W kolejnym kroku należy uruchmić phpmyadmin i zaimportować do istniejącej bazy danych plik sql, który znajduje się w głównym katalogu aplikacji.
Kontener phpmyadmin nasłuchuje domyślnie na porcie 2090, a aplikacja wyświetla się na porcie 2300. Domyślne porty można zmienić w pliku docker-compose.yml
