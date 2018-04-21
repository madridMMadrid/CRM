// CLIENTagents

/**
 * Добавить клиента.
 *
 * @param {string} name - Имя килента.
 * @param {string} phone - Телефон клиента.
 * @param {string} email - Почтовый ящик клиента.
 * @param {number} city - ID города клиента.
 */
appData.api.request('clients/store', {
    'name': 'Ivan',
    'phone': '+7 (987) 373-73-77',
    'email': 'example@mail.com',
    'city': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Изменяем информацию о клиенте.
 *
 * @param {number} clientId - ID клиента.
 * @param {string} name - Имя клиента.
 * @param {string} phone - Номер телефона.
 * @param {string} email - Почта клиента.
 */
appData.api.request('clients/edit', {
    'clientId': 2,
    'city': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Выйти.
 */
appData.api.request('clients/logout', {}, function (resp) {
    console.log(resp)
});

/**
 * Выбираем клиента по ID.
 *
 * @param {number} id - ID клиента.
 */
appData.api.request('clients/getClientById', {
    'id': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Заблокировать или разблокировтать клиента.
 *
 * @param {number} clientId - ID клиента.
 * @param {number} type - Тип действия.
 * @param {string} comment - Комментарий.
 */
appData.api.request('clients/blockClient', {
    'clientId': 1,
    'type': 1,
    'comment': 'Чё, пацаны, аниме?'
}, function (resp) {
    console.log(resp)
});

// END CLIENT


// STATISTICS

/**
 * Получаем всех новых пользователей.
 */
appData.api.request('clients/newClients', {}, function (resp) {
    console.log(resp)
});

/**
 * Получаем всех пользователей, требующих внимания.
 */
appData.api.request('clients/requireAttention', {}, function (resp) {
    console.log(resp)
});

/**
 * Получаем всех пользователей.
 *
 * @param {number} limitFrom - Номер записи первого клиента в списке.
 * @param {number} limitTo - Количество клиентов в списке.
 * @param {string} query - Поисковый запрос.
 */
appData.api.request('clients/displayClients', {
    'limitFrom': 1,
    'limitTo': 2,
    'query': ''
}, function (resp) {
    console.log(resp)
});

// END STATISTICS


// COMMENTS

/**
 * Получить комментарии к клиенту.
 *
 * @param {number} clientId - ID пакета клиента.
 * @param {number} limitFrom - Номер первого элемента.
 * @param {number} limitTo - Количество элементов.
 */
appData.api.request('clients/getClientComments', {
    'clientId': 2,
    'limitFrom': 0,
    'limitTo': 5
}, function (resp) {
    console.log(resp)
});

// END COMMENTS


// CERTIFICATES

/**
 * Добавление сертификата.
 *
 * @param {string} expiration - Дата истечения срока сертификата.
 * @param {string} comment - Комментарий к сертификату.
 * @param {number} discount - Размер скидки (проценты или деньги).
 * @param {number} type - 0 означает проценты, 1 означает деньги.
 * @param {number} clientId - Номер клиента.
 */
appData.api.request('clients/addCertificate', {
    'expiration': '2018-09-10',
    'comment': 'Чё, пацаны, аниме?',
    'discount': 20,
    'type': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Добавление сертификата.
 *
 * @param {string} expiration - Дата истечения срока сертификата.
 * @param {string} comment - Комментарий к сертификату.
 * @param {number} discount - Размер скидки (проценты или деньги).
 * @param {number} type - 0 означает проценты, 1 означает деньги.
 * @param {number} clientId - Номер клиента.
 */
appData.api.request('clients/activateCertificate', {
    'certificateNumber': 3535462,
    'clientId': 1
}, function (resp) {
    console.log(resp)
});

// END CERTIFICATES