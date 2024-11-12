/* clutch.js
 *
 *
 */

var CLU = {
    copyUrl: function(evt) {
        navigator.clipboard.writeText(evt.target.getAttribute('data')).then(() => {
          /* clipboard successfully set */
        }, () => {
          /* clipboard write failed */
            console.log("failed");
        });
    }
};

