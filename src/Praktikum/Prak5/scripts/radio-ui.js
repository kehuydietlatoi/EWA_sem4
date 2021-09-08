'use strict';

let form = document.getElementsByClassName('auto-submit-form')[0];
let elements = document.getElementsByClassName('auto-submit-btn');
for (var elem of elements) {
    elem.onclick = function () {
        form.submit();
    }
}

setInterval(() => { window.location.reload(); }, 5000);