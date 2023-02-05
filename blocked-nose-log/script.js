document.addEventListener("DOMContentLoaded", function() {
  const left = document.getElementById("left");
  const right = document.getElementById("right");

  left.onchange = () => maybeObserve(left, right);
  right.onchange = () => maybeObserve(left, right);

  document.getElementById("notes").oninput = recordsNotes;

  writeObservationsLog();
});

function maybeObserve(left, right) {
  if (left.value == "") return;
  if (right.value == "") return;

  addObservation({
    date: Date.now(),
    left: left.value,
    right: right.value
  })

  left.value = "";
  right.value = "";
}

function addObservation(observation) {
  const entries = JSON.parse(localStorage.getItem("observations") || "[]");

  writeObservations([observation, ...entries]);
}

function getObservations() {
  return JSON.parse(localStorage.getItem("observations") || "[]");
}

function writeObservations(observations) {
  localStorage.setItem("observations", JSON.stringify(observations));
  writeObservationsLog(observations);
}

function recordsNotes(event) {
  const observations = getObservations();
  if (observations.length == 0) return;

  observations[0].notes = event.target.value;
  writeObservations(observations)
}

function writeObservationsLog() {
  const table = document.querySelector("#observation-log tbody");
  while (table.lastElementChild) {
    table.removeChild(table.lastElementChild);
  }

  getObservations().forEach((entry, i) => {
    const date = new Date(entry.date).toLocaleString();

    const hasNotes = entry.notes && entry.notes != "";

    var row = document.createElement("tr");
    row.dataset.index = i;

    var cell = document.createElement("th");
    cell.textContent = date;
    cell.rowSpan = hasNotes ? "2" : "1";
    cell.scope = "rowgroup";
    row.appendChild(cell);

    var cell = document.createElement("td");
    cell.textContent = entry.left;
    cell.scope = "row";
    row.appendChild(cell);

    var cell = document.createElement("td");
    cell.textContent = entry.right;
    row.appendChild(cell);

    table.appendChild(row);

    if (hasNotes) {
      var row = document.createElement("tr");
      row.dataset.index = i;

      var cell = document.createElement("td");
      cell.textContent = entry.notes;
      cell.colSpan = "2";
      row.appendChild(cell);

      table.appendChild(row);
    }
  });
}

function backup() {
  const backup = {};
  for (i = 0; i < localStorage.length; i++) {
    const key = localStorage.key(i);
    const value = localStorage.getItem(key);
    backup[key] = escape(encodeURIComponent(value));
  }

  const fileName = `blocked-nose-log-${Date.now()}.json`;
  const blob = new Blob([JSON.style(backup)], {type: "text/plain"});
  const href = window.URL.createObjectURL(blob);

  const link = document.createElement('a');
  link.setAttribute('download', fileName);
  link.setAttribute('href', href);

  document.querySelector('body').appendChild(link);
  link.click();
  link.remove();
}
