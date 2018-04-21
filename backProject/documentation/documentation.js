/// Пакеты ///

appData.api.request('clients/changePackage', {
    'clientPackageId': 1,
    'packageId': 1,
    'priceId': 1,
    'amount': 0,
    'paymentType': 0
}, function (resp) {
    console.log(resp)
});

/**
 * Данные для продления пакета.
 *
 * @param {clientPackageId} - Номер пакета пользователя.
 * @param {amount} - Количество(?).
 * @param {number} priceId - ID цены пакета.
 * @param {paymentType} - Тип оплаты.
 */
appData.api.request('clients/packageProlongation', {
    'clientPackageId': 1,
    'amount': 3000,
    'priceId': 1,
    'paymentType': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Получаем все пакеты пользователя.
 *
 * @param {number} clientId - Номер клиента.
 */
appData.api.request('clients/getPackages', {
    'clientId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Данные для вывода возможных опций продления.
 *
 * @param {number} clientPackageId - Номер пакета клиента.
 */
appData.api.request('clients/showProlongPackage', {
    'clientPackageId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Данные для отмены изменений.
 *
 * @param {number} clientPackageId - Пакет клиента.
 */
appData.api.request('clients/cancelPackageChange', {
    'clientPackageId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Показать варианты заказа пакета.
 *
 * @param {number} clientId - ID клиента.
 * @param {number} packageId - Номер желаемого пакета.
 */
appData.api.request('clients/showAddPackage', {
    'clientId': 1,
    'packageId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Добавить пакет.
 *
 * @param {number} clientId - Номер желаемого пакета.
 * @param {number} packageId - Номер желаемого пакета.
 * @param {number} priceId - Номер желаемого пакета.
 * @param {number} amount - Номер желаемого пакета.
 * @param {number} paymentType - Номер желаемого пакета.
 */
appData.api.request('clients/addPackage', {
    'clientId': 1,
    'packageId': 1,
    'priceId': 1,
    'amount': 1,
    'paymentType': 1
}, function (resp) {
    console.log(resp)
});

/// Конец пакетов ///


// ИСТОРИЯ

/**
 * Получаем историю покупок.
 *
 * @param {number} clientId - Номер клиента.
 * @param {string} query - Поисковый запрос.
 * @param {number} limitFrom - Номер первой покупки.
 * @param {number} limitTo - Количество покупок.
 */
appData.api.request('clients/getHistory', {
    'clientId': 2,
    'query': '',
    'limitFrom': 1,
    'limitTo': 3
}, function (resp) {
    console.log(resp)
});

// КОНЕЦ ИСТОРИИ


/// Время ///

/**
 * Добавляем время.
 *
 * @param {number} clientPackageId - Номер пакета клиента.
 * @param {string} start - Начало доставки.
 * @param {string} finish - Конец доставки.
 * @param {number} interval - Интервал.
 * @param {number} weekDay - Номер дня недели.
 */
appData.api.request('clients/setTime', {
    'times': [
        {
            'clientPackageId': 33,
            'start': '21:21:21',
            'finish': '21:21:21',
            'interval': 0,
            'weekDay': 6
    },
        {
            'clientPackageId': 33,
            'start': '21:21:21',
            'finish': '21:21:21',
            'interval': 0,
            'weekDay': 7
    }
    ]
}, function (resp) {
    console.log(resp)
});

/**
 * Удаляем время.
 *
 * @param {number} timeId - Номер времени.
 */
appData.api.request('clients/unsetTime', {
    'timeId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Меняем время.
 *
 * $_POST['start']
 * $_POST['finish']
 * $_POST['interval']
 * $_POST['clientPackageId']
 */
appData.api.request('clients/changeTime', {
    'start': '13:58:02',
    'finish': '15:58:02',
    'interval': 0,
    'clientPackageId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Получаем все времена клиента.
 *
 * @param {number} clientPackageId - ID пакета клиента.
 */
appData.api.request('clients/getTimes', {
    'clientPackageId': 2
}, function (resp) {
    console.log(resp)
});

/// Конец времени ///


/// Адреса ///

/**
 * Получаем адреса пакета пользователя.
 *
 * @param {number} clientPackageId - Номер пакета клиента.
 */
appData.api.request('clients/getAddresses', {
    'clientPackageId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Добавить адрес пакету пользователя.
 *
 * @param {number} clientPackageId - Номер пакета клиента.
 * @param {string} city - Название города.
 * @param {string} street - Название улицы.
 * @param {string} building - Строение (дом).
 * @param {number} latitude - Долгота.
 * @param {string} longitude - Широта.
 * @param {string} flat - Номер кваритры.
 * @param {string} entrance - Вход (подъезд).
 * @param {string} comment - Комментарий.
 * @param {number} weekDay - Номер дня недели.
 */
appData.api.request('clients/addAddress', {
    'addresses': [
        {
            'clientPackageId': 2,
            'city': 'SPB',
            'street': 'Nevsky',
            'building': '123/1',
            'latitude': 1.1,
            'longitude': 1.2,
            'flat': 123,
            'entrance': 2,
            'comment': 'XYU',
            'weekDay': 8
    },
        {
            'clientPackageId': 2,
            'city': 'SPB',
            'street': 'Nevsky',
            'building': '124/1',
            'latitude': 2.2,
            'longitude': 2.4,
            'flat': 246,
            'entrance': 4,
            'comment': 'XYU',
            'weekDay': 9
    }
    ]
}, function (resp) {
    console.log(resp)
});

/// Конец адресов ///

/**
 * Добавляем доставку.
 *
 * @param {number} clientPackageId - Номер пакета пользователя.
 * @param {string} from - Дата начала.
 * @param {string} to - Дата конца.
 * @param {number} state - Состояние доставки.
 * @param {number} addressId - Номер адреса (исключение).
 * @param {number} timeId - Номер времени (исключение).
 */
appData.api.request('clients/setDeliveries', {
    'clientPackageId': 2,
    'from': '2018-09-10 13:00:00',
    'to': '2018-09-20 14:00:00',
    'state': 0,
    'addressId': 1,
    'timeId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Получаем доставку.
 *
 * @param {number} clientPackageId - ID пакета клиента.
 * @param {string} from - Дата начала.
 * @param {string} to - Дата конца.
 */
appData.api.request('clients/getDeliveries', {
    'clientPackageId': 2,
    'from': '2018-09-10',
    'to': '2018-09-14'
}, function (resp) {
    console.log(resp)
});


/**
 * Добавляем доставку.
 *
 * @param {number} clientPackageId - Номер пакета клиента.
 * @param {string} from - Дата первой доставки.
 * @param {string} to - Дата последней доставки.
 * @param {number} state - Заморожена ли (по умолчанию нет).
 * @param {number} addressId - Номер адреса, если указан, то это исключение.
 * @param {number} timeId - Номер времени, если указан, то это исключение.
 */
appData.api.request('clients/setDelivery', {
    'clientPackageId': 2,
    'packageId': 1,
    'from': '2018-09-10 13:00:00',
    'to': '2018-09-20 14:00:00',
    'state': 0,
    'addressId': 1,
    'timeId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Меняем доставку.
 *
 * $_POST['id']
 * $_POST['date']
 * $_POST['state']
 * $_POST['employeeId']
 * $_POST['addressId']
 * $_POST['timeId']
 */
appData.api.request('clients/changeDelivery', {
    'id': 11,
    'date': '2018-09-10',
    'state': 1,
    'addressId': 2,
    'timeId': 2
}, function (resp) {
    console.log(resp)
});

appData.api.request('clients/deleteDelivery', {
    'clientPackageId': 2,
    'from': '2018-09-10',
    'to': '2018-09-14'
}, function (resp) {
    console.log(resp)
});

/**
 * Добавить комментарий к клиенту.
 *
 * @param {number} clientId - Номер клиента.
 * @param {string} comment - Текст комментария.
 */
appData.api.request('clients/pushComment', {
    'clientId': 2,
    'comment': 'Хм'
}, function (resp) {
    console.log(resp)
});

appData.api.request('clients/changePackageComment', {
    'clientPackageId': 17,
    'comment': 'Хм'
}, function (resp) {
    console.log(resp)
});


/// Сертификаты ///

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
    'discount': 5,
    'type': 0,
    'clientId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Получение данных сертификата.
 *
 * @param {number} number - Номер сертификата.
 */
appData.api.request('clients/getCertificate', {
    'number': 9481705
}, function (resp) {
    console.log(resp)
});

/**
 * Активация сертификата.
 *
 * @param {number} certificateNumber - Номер сертификата.
 * @param {number} clientId - Номер клиента.
 */
appData.api.request('clients/activateCertificate', {
    'certificateNumber': 9481705,
    'clientId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Получить все сертификаты.
 *
 * @param {number} from - Номер первого сертификата.
 * @param {number} to - Количество сертификатов от from.
 * @param {string} query - Поисковый запрос.
 */
appData.api.request('clients/getAllCertificates', {
    'from': 1,
    'to': 1,
    'query': ''
}, function (resp) {
    console.log(resp)
});

/// Конец сертификатов ///


/// Заявки ///

/**
 * Добавить заявку.
 *
 * @param {string} name - Имя килента.
 * @param {string} phone - Телефон клиента.
 * @param {string} email - Почтовый ящик клиента.
 * @param {string} question - Вопрос клиента.
 * @param {number} packageId - Номер пакета, в котором заинтересован пользователь.
 * @param {number} priceId - Номер цены.
 * @param {number} city - Город.
 */
appData.api.request('requests/addRequest', {
    'name': 'Паша',
    'phone': '+7 (000) 000-00-00',
    'email': 'eee@eee.ee',
    'question': 'Чё, пацаны, аниме?',
    'packageId': 1,
    'priceId': 1,
    'city': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Получить заявки.
 *
 * @param {number} from - Номер первой заявки.
 * @param {number} to - Количество заявок от from.
 */
appData.api.request('clients/getRequests', {
    'limitFrom': 0,
    'limitTo': 10
}, function (resp) {
    console.log(resp)
});

/**
 * Обработать заявку.
 *
 * @param {number} from - Номер заявки.
 * @param {number} action - Тип действия (0 или 1).
 * @param {string} comment - Комментарий.
 */
appData.api.request('clients/processRequest', {
    'requestId': 2,
    'action': 0,
    'comment': 'Лох какой-то',
    'reason': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Получить список причин отказа.
 *
 */
appData.api.request('clients/showReasons', {}, function (resp) {
    console.log(resp)
});

/**
 * Получить отложенные заявки.
 */
appData.api.request('clients/deferredRequests', {}, function (resp) {
    console.log(resp)
});

// КОНЕЦ ЗАЯВОК


/// Платежи ///

/**
 * Подтвердить платёж.
 *
 * @param {number} paymentId - Номер платежа.
 * @param {number} amount - Сумма платежа.
 * @param {number} paymentType - Тип платежа (если изменился).
 */
appData.api.request('clients/confirmPayment', {
    'paymentId': 1,
    'amount': 1650,
    'paymentType': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Получить все неподтверждённые платежи.
 *
 * @param {string} dateFrom - Номер платежа.
 * @param {string} dateTo - Сумма платежа.
 * @param {string} query - Тип платежа (если изменился).
 * @param {string} limitFrom - Сумма платежа.
 * @param {string} limitTo - Тип платежа (если изменился).
 */
appData.api.request('clients/getPayments', {
    'dateFrom': '2018-09-10',
    'dateTo': '2018-09-10',
    'query': '',
    'limitFrom': 1,
    'limitTo': 2
}, function (resp) {
    console.log(resp)
});

/// Конец платежей ///


/// Долги ///

/**
 * Оплатить долг.
 *
 * @param {number} clientId - ID клиента.
 * @param {number} amount - Сумма к оплате.
 * @param {number} paymentType - Тип оплаты.
 */
appData.api.request('clients/payDebt', {
    'clientId': 20,
    'amount': 1000,
    'paymentType': 3
}, function (resp) {
    console.log(resp)
});

/// Конец долгов ///

/**
 * Лист готовки.
 */
appData.api.request('clients/cookingList', {}, function (resp) {
    console.log(resp)
});

/**
 * Get statistics.
 */
appData.api.request('clients/myStats', {}, function (resp) {
    console.log(resp)
});

/**
 * Подтверждаем пакет.
 *
 * @param {number} clientPackageId — ID пакета клиента.
 */
appData.api.request('clients/confirmPackage', {
    'clientPackageId': 3
}, function (resp) {
    console.log(resp)
});

appData.api.request('sales/getDashboard', {
    'from': 0,
    'to': 10,
    'dateFrom': '2017-11-12 12:44:05',
    'dateTo': '2017-12-12 12:44:05'
}, function (resp) {
    console.log(resp)
});

/**
 * Считаем бонусы клиента.
 *
 * @param {number} clientId - ID пакета клиента.
 */
appData.api.request('clients/clientBonusesTotal', {
    'clientId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Отбираем бонусы клиента.
 *
 * @param {number} amount - Количество денег.
 * @param {number} clientId - ID пакета клиента.
 */
appData.api.request('clients/withdrawBonuses', {
    'amount': 600,
    'clientId': 2
}, function (resp) {
    console.log(resp)
});

/**
 * Добавляем бонусы клиента.
 *
 * @param {number} amount - Количество денег.
 * @param {number} clientId - ID пакета клиента.
 * @param {string} expiration - Дата истечения срока действия бонуса.
 * @param {string} comment - Комментарий.
 */
appData.api.request('clients/setBonus', {
    'clientId': 2,
    'amount': 600,
    'expiration': '2017-09-12',
    'comment': 'Андер'
}, function (resp) {
    console.log(resp)
});

/**
 * Получаем список всех городов.
 *
 * @param {number} amount - Количество денег.
 * @param {number} clientId - ID пакета клиента.
 */
appData.api.request('clients/getCities', {}, function (resp) {
    console.log(resp)
});


// REMINDERS

/**
 * Отложить заявку.
 *
 * @param {number} requestId - ID заявки.
 */
appData.api.request('clients/defer', {
    'requestId': 1
}, function (resp) {
    console.log(resp)
});

/**
 * Получить напоминания.
 */
appData.api.request('clients/getReminders', {}, function (resp) {
    console.log(resp)
});

// END REMINDERS
