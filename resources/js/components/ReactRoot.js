import React from "react";
import ReactDOM from "react-dom";

function ReactRoot() {
    return <div className="container">Inserisci immagine per lo studio</div>;
}

export default ReactRoot;

window.addEventListener("DOMContentLoaded", () => {
    ReactDOM.render(<ReactRoot />, document.getElementById("react-root"));
});
