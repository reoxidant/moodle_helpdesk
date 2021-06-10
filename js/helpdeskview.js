function togglehistory() {
    if (historydiv && historylink) {
        historydiv = document.getElementById("issuehistory");
        historylink = document.getElementById("togglehistorylink");

        if (historydiv.className === "visiblediv") {
            historydiv.className = "hiddendiv";
            historylink.innerText = showhistory;
        } else {
            historydiv.className = "visiblediv";
            historylink.innerText = hidehistory;
        }
    }

    let historydiv, historylink;
}