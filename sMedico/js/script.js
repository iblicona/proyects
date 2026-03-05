// Get the form and the container for the stored data
const form = document.querySelector('.cons-container form');
const dataContainer = document.createElement('div');
dataContainer.classList.add('data-container');
form.parentNode.appendChild(dataContainer);

// Function to save the form data
function saveFormData(event) {
  event.preventDefault();

  // Get the form data
  const formData = new FormData(event.target);
  const data = {
    matricula: formData.get('matricula'),
    name: formData.get('name'),
    genero: formData.get('genero'),
    escolaridad: formData.get('escolaridad'),
    sintomas: formData.get('sintomas'),
    appointmentDate: formData.get('appointment-date'),
    presion: formData.get('presion'),
    temperatura: formData.get('temperatura'),
    medidosis: formData.get('medidosis')
  };

  // Create a new div to display the data
  const dataDiv = document.createElement('div');
  dataDiv.classList.add('data-item');

  // Create the HTML structure to display the data
  dataDiv.innerHTML = `
    <h3>Matrícula: ${data.matricula}</h3>
    <p>Nombre: ${data.name}</p>
    <p>Género: ${data.genero}</p>
    <p>Escolaridad: ${data.escolaridad}</p>
    <p>Síntomas y Observaciones: ${data.sintomas}</p>
    <p>Fecha de cita: ${data.appointmentDate}</p>
    <p>Presión: ${data.presion} mmHg</p>
    <p>Temperatura: ${data.temperatura}°C</p>
    <p>Medicamentos y dosis: ${data.medidosis}</p>
  `;

  // Append the new data div to the container
  dataContainer.appendChild(dataDiv);

  // Reset the form
  form.reset();
}

// Add the event listener to the form
form.addEventListener('submit', saveFormData);