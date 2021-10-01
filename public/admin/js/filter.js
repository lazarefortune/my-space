window.onload = () => {

    // On récupère le role
    const roles = document.querySelector("#role");
    
    roles.addEventListener("change", () => {
        
        var role = roles.value;

        const Url = new URL(window.location.href);
        // On lance la requête ajax
        fetch( Url.pathname + "?ajax=1&role=" + role ,  {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        }).then( 
            response => response.json()
        ).then(
            data => {
                // Récupération de la réponse
                const content = document.querySelector("#list");
                // on crée un espace pour stoquer la réponse dans le document
                var espaceTemp = document.createElement('div');
                espaceTemp.id = 'tempHTML';
                // On place notre réponse dans l'espace temporaire
                espaceTemp.innerHTML = data.content;
                // On ajoute notre espace temporaire dans le document
                document.body.appendChild( espaceTemp );
                // On recupère notre liste dans le document
                var nodeList = document.querySelector( "#tempHTML #list" );
                var MyHTML = nodeList.innerHTML;
                // On supprime notre espace temporaire
                document.body.removeChild( espaceTemp );
                // On remplace dans la liste par la nouvelle
                content.innerHTML = MyHTML;
                // document.getElementById("list").innerHTML = MyHTML;

                // On désélectionne tous les users
                document.getElementById('checkAll').checked = false ;
            }
        ).catch( e => alert(e) );
    });
}