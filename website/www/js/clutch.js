/* clutch.js
 *
 *
 */

"use strict";

var CLU = {
    modal: {},
    processModal: function(event) {
        switch (CLU.modal.action) {
            case "delete_clutcher":
                CLU.dateAlter(CLU.modal.action, CLU.modal.data);
                break;

            case "delete_date":
                CLU.dateAlter(CLU.modal.action, CLU.modal.data);
                break;

            default:
                console.log("Failed to determine resolve action:" + CLU.modal.action);
        }
    },
    init: function() {
        const modalEl = document.getElementById('mainModal')
        let maction_button = document.getElementById('modalConfirm');
        let mtitle = document.getElementById('modalTitle');
        let mbody = document.getElementById('modalBody');

        // on modal confirm button click, determine action
        maction_button.addEventListener('click', CLU.processModal);

        modalEl.addEventListener('show.bs.modal', event => {
            const data_el = event.explicitOriginalTarget;
            const action = data_el.getAttribute('action');
            let pid, date, clutcher;

            // this will determine action when modal affirmative is clicked
            CLU.modal.action = action;

            // customize modal
            switch (action) {
                case "delete_clutcher":
                    pid = data_el.getAttribute('pid');
                    date = data_el.getAttribute('date');
                    clutcher = data_el.getAttribute('clutcher');

                    // set data for action if carried out
                    CLU.modal.data = {
                        "pid": pid,
                        "date": date
                    };

                    // display modal text
                    mtitle.innerHTML = "Confirm clutcher erase";
                    mbody.innerHTML = 'Confirm you wish to erase clutcher <b class="accent2">' + clutcher + '</b> from the date <b class="accent3">' + date + '</b>.';
                    maction_button.innerHTML = "Erase";
                    break;

                case "delete_date":
                    pid = data_el.getAttribute('pid');
                    date = data_el.getAttribute('date');

                    // set data for action if carried out
                    CLU.modal.data = {
                        "pid": pid,
                        "date": date
                    };
                    // display modal text
                    mtitle.innerHTML = "Confirm date delete";
                    mbody.innerHTML = 'Confirm you wish to delete the date <b class="accent3">' + date + '</b> and any clutcher associated with it.';
                    maction_button.innerHTML = "Delete";
                    break;

                default:
                    mtitle.innerHTML = "Modal title";
                    mbody.innerHTML = "Modal body";
                    maction_button.innerHTML = "Confirm";
            }
        });
    },
    copyUrl: function(evt) {
        navigator.clipboard.writeText(evt.target.getAttribute('data')).then(() => {
          /* clipboard successfully set */
        }, () => {
          /* clipboard write failed */
            console.log("failed");
        });
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

CLU.init();

