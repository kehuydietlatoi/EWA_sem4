'use strict';

let currentCart = [];
let cartView = document.getElementById('cart');
let cartFormField = document.getElementById('cart-data');
let emptyView = document.getElementById('empty-placeholder');
let priceView = document.getElementById('total-price');
let btnSubmit = document.getElementById('btn-submit');

function createElement(html) {
    let container = document.createElement('div');
    container.innerHTML = html.trim();
    return container.firstChild;
}

function deleteFromCart(pizzaName){
    let index = currentCart.map(item => item.name).indexOf(pizzaName);
    currentCart.splice(index, 1);
    console.log(pizzaName);
    console.log(index);
    cartView.removeChild(document.getElementById(pizzaName));
    let count = currentCart.filter(el => el.name === pizzaName).length;
    if (count != 0){
        cartView.appendChild(createElement(`
            <div id="${pizzaName}" class="cart-item">
                Pizza ${pizzaName} X ${count}
                <img alt="Löschen" class="delete-button" height="22" src="img/icon-delete.svg" onclick="deleteFromCart('${pizzaName}')">
            <\/div>
            `));
    }
    onCartChanged();
}

function addToCart(pizza) {
    let count = currentCart.filter(el => el.name === pizza.name).length;
    if (count != 0) {
        cartView.removeChild(document.getElementById(pizza.name));
    }

    count++;        
    currentCart.push(pizza);
    cartView.appendChild(createElement(`
        <div id="${pizza.name}" class="cart-item">
            <span class="text-bold">${count}x</span> Pizza ${pizza.name}
            <img alt="Löschen" class="delete-button" height="22" src="img/icon-delete.svg" onclick="deleteFromCart('${pizza.name}')">
        <\/div>
        `));
    onCartChanged();
}

function deleteAllPizza(){
    cartView.innerHTML = '';
    currentCart =[];
    onCartChanged();
}

function onCartChanged() {
    // Show or hide the empty placeholder
    emptyView.style.display = currentCart.length > 0 ? 'none' : 'block';

    // Update the form data
    cartFormField.value = JSON.stringify(currentCart.map(item => item.id));

    // Recalculate total price
    priceView.innerText = currentCart.reduce((accum, item) => accum + item.price, 0).toFixed(2);

    // Enable or disable submit button
    btnSubmit.disabled = currentCart.length == 0;
}
