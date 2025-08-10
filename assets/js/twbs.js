import 'bootstrap/dist/css/bootstrap.min.css';

import { Tooltip, Toast, Popover } from "bootstrap";

// Tooltip
const tooltipList = [].slice.call(
    document.querySelectorAll('[data-toggle="tooltip"]')
);
tooltipList.map((e) => {
    return new Tooltip(e);
});

// Toast
const toastList = [].slice.call(document.querySelectorAll(".toast"));
toastList.map((e) => {
    var toast = new Toast(e, { delay: 4000 });
    toast.show();
});

// Popover
const popoverTriggerList = document.querySelectorAll(
    '[data-bs-toggle="popover"]'
);
const popoverList = [...popoverTriggerList].map((e) => new Popover(e));