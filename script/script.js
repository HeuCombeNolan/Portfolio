// Variables pour suivre l'état des différentes pages ouvertes
var p1_is_open = false;
var p2_is_open = false;
var p3_is_open = false;

// Sélection des éléments correspondant aux différentes pages
const page1 = document.querySelector(".pagePre1");
const page2 = document.querySelector(".pagePre2");
const page3 = document.querySelector(".pagePre3");

// Fonction pour ouvrir ou fermer la première page
function openPre1(){
    console.log("open");
    if(p1_is_open == false) {
        // Si la page est fermée, la rendre visible et mettre à jour l'état
        page1.style.visibility = 'visible';
        p1_is_open = true;
    } else {
        // Si la page est ouverte, la rendre invisible et mettre à jour l'état
        page1.style.visibility = 'hidden';
        p1_is_open = false;
    }
}

// Fonction pour ouvrir ou fermer la deuxième page
function openPre2(){
    console.log("open");
    if(p2_is_open == false) {
        page2.style.visibility = 'visible';
        p2_is_open = true;
    } else {
        page2.style.visibility = 'hidden';
        p2_is_open = false;
    }
}

// Fonction pour ouvrir ou fermer la troisième page
function openPre3(){
    console.log("open");
    if(p3_is_open == false) {
        page3.style.visibility = 'visible';
        p3_is_open = true;
    } else {
        page3.style.visibility = 'hidden';
        p3_is_open = false;
    }
}

// Fonction pour fermer la première page
function close1() {
    p1_is_open = false;
    page1.style.visibility = 'hidden';
}

// Fonction pour fermer la deuxième page
function close2() {
    p2_is_open = false;
    page2.style.visibility = 'hidden';
}

// Fonction pour fermer la troisième page
function close3() {
    p3_is_open = false;
    page3.style.visibility = 'hidden';
}
