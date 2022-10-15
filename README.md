# docker-wordpress-3.9.2
A Wordpress Project 3.9.2 runnable with docker

NB: The Wordpress website will start in English, other languages pack needs to be installed manually.

## Run Containers

Go into the Project folder and run the command:

```
docker-compose up --build -d
```

## Language Pack installation

1. **Download:** Go on the official Wordpress website, in my case it's: https://it.wordpress.org/download/releases/, if you want install a different one change "https://**it**" substring with the one you want, for example https://es.wordpress.org/download/releases/ for the spanish official website, then download the 3.9.2 zip release. 
Unzip the file and copy the entire content of the folded wp-content/languages in the project directory.

2. **Install the language pack** Copy the languages folder into wordpress docker container with command:

```
docker cp languages wordpress:/var/www/html/wp-content/
```

then update the file wp-config.php into the container and find the string **define ('WPLANG', '')** and update the string with name of the .mo file of your language ('WPLANG', 'it_IT');

3. **Restart the container**

