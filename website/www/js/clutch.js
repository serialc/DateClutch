/* clutch.js
 *
 *
 */

"use strict";

var CLU = {
    copyUrl: function(evt) {
        navigator.clipboard.writeText(evt.target.getAttribute('data')).then(() => {
          /* clipboard successfully set */
        }, () => {
          /* clipboard write failed */
            console.log("failed");
        });
    },
    confirmDateAlter: function(action, data) {
        if (confirm("Confirm you wish to " + action + " for date " + data.date)) {
            this.dateAlter(action, data);
        }
    },
    dateAlter: function(action, data) {
        fetch("/api/" + action, {
            method: 'POST',
            cache: 'no-cache',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then((response) => response.json())
        .then((resp) => {
            if ( resp === "success" ) {
                let delem;
                switch(action) {
                    case 'delete_date':
                        delem = document.getElementById('date_' + data.date);
                        delem.parentElement.removeChild(delem);
                        break;

                    case 'delete_clutcher':
                        delem = document.getElementById('clutcher_' + data.date);
                        delem.parentElement.removeChild(delem);
                        break;

                    case 'add_date':
                        location.reload();
                        break;
                }
            } else {
                // make Toast to warn of failure - do it later
            }
        });
    },
};

