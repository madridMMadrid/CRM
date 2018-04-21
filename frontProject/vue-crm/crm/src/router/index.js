import Vue from 'vue';
import Router from 'vue-router';

// Vue plugins
// iview
import iView from 'iview';
import 'iview/dist/styles/iview.css';

// others
import Multiselect from 'vue-multiselect'
import VueGoodWizard from 'vue-good-wizard';
import VueMaskedInput from 'vue-masked-input';
import VeeValidate, { Validator } from 'vee-validate';
import VeeRussian from 'vee-validate/dist/locale/ru';
import Acl from 'vue-acl';
import vmodal from 'vue-js-modal';
import infiniteScroll from 'vue-infinite-scroll';


// Russian language support
import VueI18n from 'vue-i18n';
import ru from 'iview/dist/locale/ru-RU';

// components
import ClientCard from '@/components/clientCard';

    // forms
    import SpinnerBtn from '@/components/forms/button-spinner';
    import Input from '@/components/forms/input';
    import Select_Bootstrap from '@/components/forms/select-bootstrap';
    import DateRangePicker from '@/components/forms/date-range-picker';

    // widgets
    import Packages from '@/components/widgets/packages';
    import Package from '@/components/widgets/package';
    import PackageList from '@/components/widgets/package-list';
    import Calendarity from '@/components/widgets/calendarity';

        // client
        import PackageTab from '@/components/widgets/client/package-tab';
        import ExtensionPackage from '@/components/widgets/client/extensionPackage';
        import Comments from '@/components/widgets/client/comments';
        import PurchaeHistory from '@/components/widgets/client/purchase-history';
        import AdderssPicker from '@/components/widgets/client/address-picker';
        import TimesPicker from '@/components/widgets/client/times-picker';
        import Certificate from '@/components/widgets/client/certificate';

    // stats
    import StatsLabel from '@/components/stats/label';
    import StatsDonut from '@/components/stats/donut';
    import StatsRevenue from '@/components/stats/revenue';

    // markup
    import PageHeader from '@/components/markup/page-header';
    import IncomeNode from '@/components/markup/income-node';
    import CertificateNode from '@/components/markup/certificate-node';
    import PacakgeLabel from '@/components/markup/package-label';
    import PaymentType from '@/components/markup/payment-type';
    import paymentBonuses from '@/components/markup/payment-bonuses';

// pages
import Login from '@/pages/login';
import Dashboard from '@/pages/dashboard';
import SalesStats from '@/pages/sales-stats';
import ClientDatabase from '@/pages/client-database';
import Client from '@/pages/client';
import DashboardIncome from '@/pages/dashboard-income';
import CoachCardPage from '@/pages/coachCardPage';
import CoachCardStatistics from '@/pages/coachCardStatistics';


import Coach from '@/pages/Coach';
import Stock from '@/pages/Stock';
import Company from '@/pages/Company';

import HOSDashboard from '@/pages/HOSDashboard';
import Certificates from '@/pages/certificates';

Vue.component('multiselect', Multiselect);

Vue.component('client-card', ClientCard);

// form components
Vue.component('button-spinner', SpinnerBtn);
Vue.component('input-bootstrap', Input);
Vue.component('select-bootstrap', Select_Bootstrap);
Vue.component('date-range-picker', DateRangePicker);

Vue.component('comments', Comments);
Vue.component('packages', Packages);
Vue.component('package', Package);
Vue.component('package-list', PackageList);
Vue.component('address-picker', AdderssPicker);
Vue.component('times-picker', TimesPicker);
Vue.component('certificate', Certificate);
Vue.component('calendarity', Calendarity);
Vue.component('purchase-history', PurchaeHistory);
Vue.component('package-tab', PackageTab);

// stats components
Vue.component('stats-label', StatsLabel);
Vue.component('stats-donut', StatsDonut);
Vue.component('stats-revenue', StatsRevenue);

// markup components
Vue.component('page-header', PageHeader);
Vue.component('income-node', IncomeNode);
Vue.component('certificate-node', CertificateNode);
Vue.component('package-label', PacakgeLabel);
Vue.component('extensionPackage', ExtensionPackage);
Vue.component('payment-type', PaymentType);
Vue.component('payment-bonuses', paymentBonuses);

Vue.component('coach', Coach);
Vue.component('stock', Stock);
Vue.component('company', Company);
Vue.component('coachCardPage', CoachCardPage);
Vue.component('coachCardStatistics', CoachCardStatistics);

Vue.use(Router);

Vue.use(VueI18n);

Vue.use(iView);


// iview initialization
import { locale, Button, Table } from 'iview';
Vue.config.lang = 'ru';
locale(ru);

Vue.component('Button', Button);
Vue.component('Table', Table);

// Vue good wizard - пошаговое заполнение больших форм
// Docs: https://github.com/xaksis/vue-good-wizard
Vue.use(VueGoodWizard);

// Валлидация телефонного номера masked
// https://github.com/niksmr/vue-masked-input
console.log(VueMaskedInput);
Vue.component('masked-input', VueMaskedInput);

// Vue validate - валидатор заполненных полей
// Docs: http://vee-validate.logaretm.com/
// Validator.localize('ru', VeeRussian); 
// Vue.use(VeeValidate);

// Vue Accesss control level - даем доступ только тем, кто этого заслуживает
// Docs: https://github.com/leonardovilarinho/vue-acl
// Vue.use( Acl, { router: Router, init: 'public' } )

// Vue Modal - модалы
// Docs: https://www.npmjs.com/package/vue-js-modal
// Vue.use(vmodal)

// Vue Infinite Scroll - модалы
// Docs: https://github.com/ElemeFE/vue-infinite-scroll
// Vue.use(infiniteScroll)

export default new Router({
    mode: 'history',
  routes: [
    { 
        path: '/', 
        redirect: '/dashboard',
        name: 'Main',
        
        noLoginRequired: true,
        permissions: [1,3,5],
    },
    {
        path: '/login/',
        name: 'Login',
        component: Login,
        noLoginRequired: true
    }, {
        path: '/dashboard/',
        name: 'Dashboard',
        component: Dashboard,

        naming: 'Мои задачи',
        icon: 'icon-home4',
        permissions: [1,3,5],
    }, {
        path: '/dashboard-income/',
        name: 'DashboardIncome',
        component: DashboardIncome,

        naming: 'Прием финансов',
        icon: 'icon-cash3',
        permissions: [3,5],
    }, {
        path: '/certificates/',
        name: 'Certificates',
        component: Certificates,

        naming: 'Сертификаты',
        icon: 'icon-cash3',
        permissions: [2,5],
    }, {
        path: '/HOSDashboard/',
        name: 'HOSDashboard',
        component: HOSDashboard,

        naming: 'Панель управления',
        icon: 'icon-chart',
        permissions: [3,5],
    }, {
        path: '/sales-stats/',
        name: 'SalesStats',
        component: SalesStats,

        naming: 'Моя статистика',
        icon: 'icon-graph',
        permissions: [1,3,5],
    }, {
        path: '/clients/',
        name: 'ClientDatabase',
        component: ClientDatabase,

        naming: 'База клиентов',
        icon: 'icon-vcard',
        permissions: [1,3,5],
    },{
        path: '/client/:id',
        name: 'Client',
        component: Client,

        naming: 'Клиент',
        permissions: [1,3,5],
    },{
        path: '/coach/',
        name: 'Coach',
        component: Coach,

        naming: 'Тренеры (Анна.К)',
        icon: 'icon-mustache',
        permissions: [5],
    },{
        path: '/stock/',
        name: 'Stock',
        component: Stock,

        naming: 'Акции (Анна.К)',
        icon: 'icon-piggy-bank',
        permissions: [5],
    },{
        path: '/company/',
        name: 'Company',
        component: Company,

        naming: 'Компании (Анна.К)',
        icon: 'icon-bars-alt',
        permissions: [5],
    },{
        path: '/coachCardPage/',
        name: 'CoachCardPage',
        component: CoachCardPage,

        permissions: [5],
    },{
        path: '/coachCardStatistics/',
        name: 'CoachCardStatistics',
        component: CoachCardStatistics,

        permissions: [5],
    },
  ],
});
