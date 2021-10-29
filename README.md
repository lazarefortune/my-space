# My Space
> My Space est un espace personnel contenant des applications pour mes besoins personnels

## Configuration du projet

- Récupération en local
  ``` 
  git init
  git remote add origin https://github.com/lazarefortune/my-space.git
  git pull origin
  git checkout main
  ```
  
- Installation du projet en local 
  ( *NB : Assurer vous d'avoir composer sur votre machine* )
  
  ```
  composer install
  php bin/console doctrine:migrations:migrate 
  ```
 Et voilà, le projet est en route... 
 ;) Enjoy !!!
  
