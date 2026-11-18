// TaskViewModel.js - O cérebro do Front-end
class TaskViewModel {
    constructor() {
        this.tasks = []; // O "Estado" da aplicação (Model no lado do cliente)
        this.init();
    }

    async init() {
        // Mapeia elementos da View
        this.listElement = document.getElementById('taskList');
        this.formElement = document.getElementById('taskForm');
        this.inputElement = document.getElementById('taskTitle');
        this.descriptionElement = document.getElementById('taskDescription');
        this.dueDateElement = document.getElementById('taskDueDate');
        this.addBtn = document.getElementById('addBtn');
        this.cancelBtn = document.getElementById('cancelBtn');

        this.formElement.onsubmit = event => {
            event.preventDefault();
            this.saveTask();
        };
        this.cancelBtn.onclick = () => this.resetForm();

        try {
            await this.fetchTasks();
        } catch (error) {
            alert(error.message);
        }
    }

    async request(url, options) {
        const response = await fetch(url, options);
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.error || 'A operação não foi concluída.');
        }
        return result;
    }

    async fetchTasks() {
        this.tasks = await this.request('api.php?action=list');
        this.render();
    }

    async saveTask() {
        const task = {
            title: this.inputElement.value,
            description: this.descriptionElement.value,
            due_date: this.dueDateElement.value
        };
        const isEditing = this.editingTaskId !== undefined;
        const action = isEditing ? 'update' : 'create';
        if (isEditing) task.id = this.editingTaskId;

        try {
            await this.request(`api.php?action=${action}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(task)
            });

            this.resetForm();
            await this.fetchTasks();
        } catch (error) {
            alert(error.message);
        }
    }

    async deleteTask(id) {
        try {
            await this.request(`api.php?action=delete&id=${encodeURIComponent(id)}`);
            await this.fetchTasks();
        } catch (error) {
            alert(error.message);
        }
    }

    async completeTask(id) {
        try {
            await this.request(`api.php?action=complete&id=${encodeURIComponent(id)}`);
            await this.fetchTasks();
        } catch (error) {
            alert(error.message);
        }
    }

    editTask(task) {
        this.editingTaskId = task.id;
        this.inputElement.value = task.title;
        this.descriptionElement.value = task.description || '';
        this.dueDateElement.value = task.due_date;
        this.addBtn.textContent = 'Salvar alterações';
        this.cancelBtn.hidden = false;
        this.inputElement.focus();
    }

    resetForm() {
        this.editingTaskId = undefined;
        this.formElement.reset();
        this.addBtn.textContent = 'Adicionar';
        this.cancelBtn.hidden = true;
    }

    render() {
        this.listElement.innerHTML = '';
        this.tasks.forEach(task => {
            const li = document.createElement('li');
            li.className = task.done ? 'done' : '';
            const title = document.createElement('span');
            title.textContent = task.title;
            li.appendChild(title);

            const actions = document.createElement('div');
            if (!task.done) {
                const completeButton = document.createElement('button');
                completeButton.textContent = 'Concluir';
                completeButton.onclick = () => this.completeTask(task.id);
                actions.appendChild(completeButton);

                const editButton = document.createElement('button');
                editButton.textContent = 'Editar';
                editButton.onclick = () => this.editTask(task);
                actions.appendChild(editButton);
            }

            const deleteButton = document.createElement('button');
            deleteButton.textContent = 'Excluir';
            deleteButton.onclick = () => this.deleteTask(task.id);
            actions.appendChild(deleteButton);
            li.appendChild(actions);
            this.listElement.appendChild(li);
        });
    }
}

// Instancia a ViewModel
const vm = new TaskViewModel();