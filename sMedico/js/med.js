// Medicamentos

const addForm = document.getElementById('add-form');
const searchInput = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const inventoryTable = document.getElementById('inventory-table');
const inventoryTbody = document.getElementById('inventory-tbody');

let medicamentos = [];

function renderMedicamentos() {
  const tbody = inventoryTbody;
  tbody.innerHTML = '';
  
  medicamentos.forEach(medicamento => {
    const row = document.createElement('tr');
    
    const nameCell = document.createElement('td');
    nameCell.textContent = medicamento.name;
    row.appendChild(nameCell);
    
    const genericNameCell = document.createElement('td');
    genericNameCell.textContent = medicamento.genericName;
    row.appendChild(genericNameCell);
    
    const doseCell = document.createElement('td');
    doseCell.textContent = medicamento.dose;
    row.appendChild(doseCell);
    
    const quantityCell = document.createElement('td');
    quantityCell.textContent = medicamento.quantity;
    row.appendChild(quantityCell);
    
    const presentationCell = document.createElement('td');
    presentationCell.textContent = medicamento.presentation;
    row.appendChild(presentationCell);
    
    const actionCell = document.createElement('td');
    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'Eliminar';
    deleteButton.classList.add('delete-button');
    deleteButton.addEventListener('click', () => decrementMedicamento(medicamento));
    actionCell.appendChild(deleteButton);
    row.appendChild(actionCell);
    
    tbody.appendChild(row);
  });
}

function addMedicamento(event) {
  event.preventDefault();
  
  const name = document.getElementById('name').value;
  const genericName = document.getElementById('generic-name').value;
  const dose = document.getElementById('dose').value;
  const quantity = parseInt(document.getElementById('quantity').value);
  const presentation = parseInt(document.getElementById('presentation').value);
  
  const existingMedicamento = medicamentos.find(m => m.name === name && m.genericName === genericName && m.dose === dose);
  if (existingMedicamento) {
    existingMedicamento.quantity += quantity;
  } else {
    medicamentos.push({ name, genericName, dose, quantity, presentation });
  }
  
  renderMedicamentos();
  addForm.reset();
}

function decrementMedicamento(medicamento) {
  const index = medicamentos.indexOf(medicamento);
  if (index !== -1) {
    if (medicamentos[index].quantity > 1) {
      medicamentos[index].quantity--;
    } else {
      medicamentos.splice(index, 1);
    }
    renderMedicamentos();
  }
}

function searchMedicamentos() {
  const searchTerm = searchInput.value.toLowerCase();
  const filteredMedicamentos = medicamentos.filter(medicamento =>
    medicamento.name.toLowerCase().includes(searchTerm) ||
    medicamento.genericName.toLowerCase().includes(searchTerm)
  );
  medicamentos = filteredMedicamentos;
  renderMedicamentos();
}

addForm.addEventListener('submit', addMedicamento);
searchButton.addEventListener('click', searchMedicamentos);
