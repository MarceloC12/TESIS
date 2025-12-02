// --- CÓDIGO CORREGIDO PARA script.js ---

// Se envuelve la lógica del botón "Cargar Más" en una condición
// para que solo se ejecute si el botón existe en la página.
let loadMoreBtn = document.querySelector('#load-more');
if (loadMoreBtn) {
    let currentItem = 8;
    loadMoreBtn.onclick = () => {
        let boxes = [...document.querySelectorAll('.box-container .box')];
        for(var i = currentItem; i < currentItem + 4; i++){ 
            if (boxes[i]) {
                boxes[i].style.display = 'inline-block';
            }
        }
        currentItem += 4;
        if(currentItem >= boxes.length) {
            loadMoreBtn.style.display =  'none';
        }
    } 
}

// --- El resto del código del carrito permanece igual ---

//carrito
const carrito = document.getElementById('carrito');
const elementos1 = document.getElementById('lista-1');
const lista = document.querySelector('#lista-carrito tbody');
const vaciarcarritoBtn = document.getElementById('vaciar-carrito');

cargarEventListeners();

function cargarEventListeners() {
    // Se asegura de que 'elementos1' exista antes de añadir el listener
    if (elementos1) {
        elementos1.addEventListener('click', comprarElemento);
    }
    carrito.addEventListener('click', eliminarElemento);
    vaciarcarritoBtn.addEventListener('click', vaciarCarrito);
}

function comprarElemento(e) {
    e.preventDefault();
    if(e.target.classList.contains('agregar-carrito')) {
        const elemento = e.target.parentElement.parentElement;
        leerDatosElemento(elemento);
    }
}

function leerDatosElemento(elemento) {
    const infoElemento = {
        imagen: elemento.querySelector('img').src,
        titulo: elemento.querySelector('h3').textContent,
        precio: elemento.querySelector('.precio').textContent,
        id: elemento.querySelector('a').getAttribute('data-id'),
    }
    insertarCarrito(infoElemento);
}

function insertarCarrito(elemento) {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td>
            <img src="${elemento.imagen}" width=100 height=150px>
        </td>
        <td>
            ${elemento.titulo}
        </td>
        <td>
            ${elemento.precio}
        </td>
        <td>
            <a href="#" class="borrar" data-id="${elemento.id}">X</a>
        </td>
    `;
    lista.appendChild(row);
}

function eliminarElemento(e) {
    e.preventDefault();
    let elemento,
        elementoId;

    if(e.target.classList.contains('borrar')) {
        e.target.parentElement.parentElement.remove();
        elemento = e.target.parentElement.parentElement;
        elementoId = elemento.querySelector('a').getAttribute('data-id');
    }
}

function vaciarCarrito() {
    while(lista.firstChild) {
        lista.removeChild(lista.firstChild);
    }
    return false;
}