import React from "react";
import ReactDOM from "react-dom";

function ReactRoot() {
    return (
        <div className="container">
            AAAAAAAAAAAAAAAAAAAAAAAAA
            <div className="row justify-content-center">
                <div className="col-md-8">
                    <div className="card">
                        <div className="card-header">Example Component</div>

                        <div className="card-body">
                            I'm an example component!
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default ReactRoot;

ReactDOM.render(<ReactRoot />, document.getElementById("react-root"));
