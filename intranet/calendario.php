<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento de Salas</title>
    <!-- CSS do FullCalendar -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.css" rel="stylesheet" />
    <!-- Biblioteca jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- JS do FullCalendar -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.5/index.global.min.js"></script>
    <!-- Estilos personalizados -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        h1 {
            color: #333;
            margin: 20px 0;
        }
        #calendar-container {
            display: flex;
            justify-content: space-between;
            width: 80%;
            margin: 20px;
            flex-wrap: wrap;
        }
        #calendar {
            flex: 1;
            min-width: 60%;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 10px;
        }
        #eventList {
            flex: 1;
            min-width: 250px;
            max-width: 300px;
            background-color: #fff;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            padding: 15px;
            overflow-y: auto;
            max-height: 500px;
            margin-top: 20px;
        }
        #back-button {
            position: absolute;
            top: 10px;
            left: 10px;
            padding: 10px 15px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        #back-button:hover {
            background-color: #0056b3;
        }

        /* Estilos do modal */
        #eventModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            z-index: 999;
            animation: fadeIn 0.3s ease-in-out;
        }
        .modal-content {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            width: 450px;
            max-width: 100%;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            animation: slideIn 0.5s ease-in-out;
        }
        .modal-content h3 {
            margin-bottom: 20px;
            font-size: 1.5em;
            color: #333;
        }
        .modal-content input,
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1em;
        }
        .modal-content button {
            padding: 12px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            width: 100%;
            margin-top: 10px;
        }
        .modal-content button:hover {
            background-color: #0056b3;
        }
        .close-btn {
            background-color: #f44336;
            color: white;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1.1em;
            border: none;
        }
        .close-btn:hover {
            background-color: #d32f2f;
        }

        /* Animação do modal */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translateY(-30px); }
            to { transform: translateY(0); }
        }

        .event-list-item {
            padding: 8px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 8px;
        }
        .event-list-item:last-child {
            border-bottom: none;
        }
    </style>
</head>
<body>
    <h1>Agendamento de Salas</h1>
    <button id="back-button" onclick="window.location.href='home.php'">Voltar</button>
    
    <div id="calendar-container">
        <div id="calendar"></div>
        <div id="eventList">
            <h3>Eventos Agendados</h3>
            <div id="eventListContainer"></div>
        </div>
    </div>

    <!-- Modal para adicionar o evento -->
    <div id="eventModal">
        <div class="modal-content">
            <h3>Adicionar Agendamento</h3>
            <input type="text" id="eventTitle" placeholder="Título do agendamento" required />
            <textarea id="eventDescription" placeholder="Descrição (opcional)"></textarea>
            <label for="eventStartTime">Início:</label>
            <select id="eventStartTime">
                <option value="08:00">08:00</option>
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
                <option value="11:00">11:00</option>
                <option value="12:00">12:00</option>
                <option value="13:00">13:00</option>
                <option value="14:00">14:00</option>
                <option value="15:00">15:00</option>
                <option value="16:00">16:00</option>
                <option value="17:00">17:00</option>
            </select>
            <label for="eventEndTime">Término:</label>
            <select id="eventEndTime">
                <option value="08:00">08:00</option>
                <option value="09:00">09:00</option>
                <option value="10:00">10:00</option>
                <option value="11:00">11:00</option>
                <option value="12:00">12:00</option>
                <option value="13:00">13:00</option>
                <option value="14:00">14:00</option>
                <option value="15:00">15:00</option>
                <option value="16:00">16:00</option>
                <option value="17:00">17:00</option>
            </select>
            <button id="saveEvent">Salvar Agendamento</button>
            <button class="close-btn" id="closeModal">Fechar</button>
        </div>
    </div>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var eventListContainer = document.getElementById('eventListContainer');
    var modal = document.getElementById('eventModal');
    var closeModalBtn = document.getElementById('closeModal');
    var saveEventBtn = document.getElementById('saveEvent');
    var eventTitle = document.getElementById('eventTitle');
    var eventDescription = document.getElementById('eventDescription');
    var eventStartTime = document.getElementById('eventStartTime');
    var eventEndTime = document.getElementById('eventEndTime');
    var selectedStart = null;

    var events = []; // Array para armazenar os eventos localmente.

    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'pt-br',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        selectable: true,
        select: function(info) {
            selectedStart = info.startStr; // Pega a data selecionada.
            modal.style.display = 'flex'; // Mostra o modal.
        },
        events: 'get_events.php', // URL para carregar eventos salvos no servidor.
    });

    closeModalBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    saveEventBtn.addEventListener('click', function() {
        var newEvent = {
            title: eventTitle.value,
            start: selectedStart + 'T' + eventStartTime.value, // Formato ISO8601.
            end: selectedStart + 'T' + eventEndTime.value, // Formato ISO8601.
            description: eventDescription.value
        };

        if (!newEvent.title || !newEvent.start || !newEvent.end) {
            alert("Por favor, preencha todos os campos obrigatórios.");
            return;
        }

        // Envia os dados do evento para o servidor.
        $.ajax({
            url: 'add_event.php', // Script PHP para salvar o evento.
            type: 'POST',
            data: newEvent,
            success: function(response) {
                try {
                    var jsonResponse = JSON.parse(response);
                    if (jsonResponse.success) {
                        alert(jsonResponse.success);

                        // Adiciona o evento ao calendário e à lista de eventos.
                        calendar.addEvent(newEvent);
                        events.push(newEvent);
                        updateEventList();

                        // Fecha o modal e limpa os campos.
                        modal.style.display = 'none';
                        clearModalFields();
                    } else if (jsonResponse.error) {
                        alert(jsonResponse.error);
                    }
                } catch (error) {
                    console.error("Erro ao processar resposta: ", error);
                    alert("Erro inesperado ao salvar o evento.");
                }
            },
            error: function(xhr, status, error) {
                console.error("Erro ao salvar o evento: " + error);
                alert("Erro ao salvar o evento. Tente novamente.");
            }
        });
    });

    function updateEventList() {
        eventListContainer.innerHTML = '';
        events.forEach(function(event) {
            var eventItem = document.createElement('div');
            eventItem.classList.add('event-list-item');
            eventItem.innerHTML = `
                <strong>${event.title}</strong><br>
                ${new Date(event.start).toLocaleString()} - ${new Date(event.end).toLocaleString()}<br>
                ${event.description || 'Sem descrição'}
            `;
            eventListContainer.appendChild(eventItem);
        });
    }

    function clearModalFields() {
        eventTitle.value = '';
        eventDescription.value = '';
        eventStartTime.value = '08:00';
        eventEndTime.value = '08:00';
    }

    // Carrega os eventos existentes do servidor.
    $.ajax({
        url: 'get_events.php', // Script PHP para retornar eventos em JSON.
        type: 'GET',
        success: function(response) {
            try {
                var jsonResponse = JSON.parse(response);
                if (Array.isArray(jsonResponse)) {
                    events = jsonResponse; // Atualiza o array local com os eventos do servidor.
                    jsonResponse.forEach(event => {
                        calendar.addEvent(event); // Adiciona eventos ao calendário.
                    });
                    updateEventList(); // Atualiza a lista de eventos.
                } else {
                    console.error("Resposta inesperada: ", jsonResponse);
                }
            } catch (error) {
                console.error("Erro ao processar eventos: ", error);
            }
        },
        error: function(xhr, status, error) {
            console.error("Erro ao carregar eventos: " + error);
        }
    });

    calendar.render();
});

    </script>
</body>
</html>
