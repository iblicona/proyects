// Material clínico

const addForm = document.getElementById('add-form');
const searchInput = document.getElementById('search-input');
const searchButton = document.getElementById('search-button');
const inventoryTable = document.getElementById('inventory-table');
const inventoryTbody = document.getElementById('inventory-tbody');

let materiales = [];

function renderMateriales() {
  const tbody = inventoryTbody;
  tbody.innerHTML = '';
  
  materiales.forEach(material => {
    const row = document.createElement('tr');
    
    const nameCell = document.createElement('td');
    nameCell.textContent = material.name;
    row.appendChild(nameCell);
    
    const typeCell = document.createElement('td');
    typeCell.textContent = material.type;
    row.appendChild(typeCell);
    
    const quantityCell = document.createElement('td');
    quantityCell.textContent = material.quantity;
    row.appendChild(quantityCell);
    
    const actionCell = document.createElement('td');
    const deleteButton = document.createElement('button');
    deleteButton.textContent = 'Eliminar';
    deleteButton.classList.add('delete-button');
    deleteButton.addEventListener('click', () => decrementMaterial(material));
    actionCell.appendChild(deleteButton);
    row.appendChild(actionCell);
    
    tbody.appendChild(row);
  });
}

function addMaterial(event) {
  event.preventDefault();
  
  const name = document.getElementById('name').value;
  const type = document.getElementById('disposable').value;
  const quantity = parseInt(document.getElementById('quantity').value);
  
  const existingMaterial = materiales.find(m => m.name === name && m.type === type);
  if (existingMaterial) {
    existingMaterial.quantity += quantity;
  } else {
    materiales.push({ name, type, quantity });
  }
  
  renderMateriales();
  addForm.reset();
}

function decrementMaterial(material) {
  const index = materiales.indexOf(material);
  if (index !== -1) {
    if (materiales[index].quantity > 1) {
      materiales[index].quantity--;
    } else {
      materiales.splice(index, 1);
    }
    renderMateriales();
  }
}

function searchMateriales() {
  const searchTerm = searchInput.value.toLowerCase();
  const filteredMateriales = materiales.filter(material =>
    material.name.toLowerCase().includes(searchTerm)
  );
  materiales = filteredMateriales;
  renderMateriales();
}

addForm.addEventListener('submit', addMaterial);
searchButton.addEventListener('click', searchMateriales)