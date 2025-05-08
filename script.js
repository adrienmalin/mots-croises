async function sha256(text) {
  const encoder = new TextEncoder();
  const data = encoder.encode(text);
  const hashBuffer = await crypto.subtle.digest("SHA-256", data);
  const hashArray = Array.from(new Uint8Array(hashBuffer));
  return hashArray.map((b) => b.toString(16).padStart(2, "0")).join("");
}

let inputs = Array.from(grilleForm.querySelectorAll(".grille input"));
let largeur = Number(colonnes.value);
let nb_cases = inputs.length;
let index = 0;
for (let input of inputs) {
  input.index = index++;
  input.x = input.index % largeur;
  input.y = Math.floor(input.index / largeur);

  input.onfocus = function (event) {
    for (li of document.querySelectorAll(
      `.definitions.horizontales > ol > li:nth-child(${
        input.y + 1
      }), .definitions.verticales > ol > li:nth-child(${input.x + 1})`
    )) {
      li.classList.add("selectionee");
    }
    for (li of document.querySelectorAll(
      `.definitions.horizontales > ol > li:not(:nth-child(${
        input.y + 1
      })), .definitions.verticales > ol > li:not(:nth-child(${input.x + 1}))`
    )) {
      li.classList.add("non-selectionee");
    }

    input.select();
  };

  input.onkeydown = function (event) {
    next_input = null;
    switch (event.key) {
      case "ArrowUp":
        next_input = inputs[(input.index - largeur + nb_cases) % nb_cases];
        break;
      case "ArrowDown":
        next_input = inputs[(input.index + largeur) % nb_cases];
        break;
      case "ArrowLeft":
        next_input = inputs[(input.index - 1 + nb_cases) % nb_cases];
        break;
      case "ArrowRight":
        next_input = inputs[(input.index + 1) % nb_cases];
        break;
    }
    if (next_input) {
      next_input.focus();
      next_input.select();
      event.preventDefault();
    }
  };

  input.oninput = function (event) {
    this.value = this.value.toUpperCase();
    if (!input.checkValidity()) {
      input.value = "";
    }
    if (
      inputs.every((input) => input.value.length == 1) &&
      grilleForm.checkValidity()
    ) {
      sha256(inputs.map((input) => input.value).join("")).then((hash) => {
        if (hash == solution_hashee.value) {
          if (confirm("Bravo !\nUne nouvelle partie ?")) {
            grilleForm.submit();
          }
        }
      });
    }
  };

  input.onblur = function (event) {
    for (li of document.querySelectorAll(
      `.definitions.horizontales > ol > li:nth-child(${
        input.y + 1
      }), .definitions.verticales > ol > li:nth-child(${input.x + 1})`
    )) {
      li.classList.remove("selectionee");
    }
    for (li of document.querySelectorAll(
      `.definitions.horizontales > ol > li:not(:nth-child(${
        input.y + 1
      })), .definitions.verticales > ol > li:not(:nth-child(${input.x + 1}))`
    )) {
      li.classList.remove("non-selectionee");
    }
  };
}

for (let input of grilleForm.querySelectorAll(".nouvelle-grille input")) {
  input.onfocus = function (event) {
    input.name = input.id;
    input.select();
  };
}
