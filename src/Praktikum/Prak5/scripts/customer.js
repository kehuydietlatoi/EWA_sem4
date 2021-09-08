'use strict';

var container = document.getElementById('order-container');

function genAttr(order, min) {
    return order.status >= min ? 'done' : '';
}

function createOrder(order) {
    let pizza = document.createElement('div');
    pizza.classList.add('pizza');
    // Herr Hahn hat innerHTML erlaubt ;-)
    pizza.innerHTML = `
    <img alt="" src="${order.picture}" width="180">
    <div class="pizza-meta">
        <h2>Bestellung ${order.orderId} - Pizza ${order.name}</h2>

        <div class="order-state-container">
            <img alt="Bestellt" class="order-state done" src="img/icon-cart-white.svg">
            <span class="order-line ${genAttr(order, 1)}"></span>
            
            <img alt="Zubereitung" class="order-state ${genAttr(order, 1)}" src="img/icon-blender-white.svg">
            <span class="order-line ${genAttr(order, 2)}"></span>

            <img alt="Auslieferung" class="order-state ${genAttr(order, 3)}" src="img/icon-shipping-white.svg">
            <span class="order-line ${genAttr(order, 4)}"></span>

            <img alt="Zugestellt" class="order-state ${genAttr(order, 4)}" src="img/icon-all-done-white.svg">
        </div>
    </div>
    `;
    return pizza;
}

function process(json) {
    // Clear all child nodes
    for (let child of container.childNodes)
        child.remove();

    // Parse new orders
    let orders = JSON.parse(json);
    for (let order of orders) {
        container.appendChild(createOrder(order));
    }

    if (orders.length == 0) {
        let emptyMessage = document.createElement('p');
        emptyMessage.classList.add('text-center');
        emptyMessage.classList.add('text-muted');
        emptyMessage.innerText = "Du hast noch keine Bestellungen aufgegeben.";
        container.appendChild(emptyMessage);
    }
}

function reload() {
    let request = new XMLHttpRequest();
    request.open('GET', 'customerAjax.php');
    request.onreadystatechange = () => {
        if (request.readyState != XMLHttpRequest.DONE)
            return;

        if (request.status == 200)
            process(request.responseText);
        else 
            console.error('Failed to fetch data');
    }
    request.send();
}

reload();
setInterval(reload, 2000);