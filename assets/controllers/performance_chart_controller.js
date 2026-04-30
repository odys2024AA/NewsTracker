import { Controller } from '@hotwired/stimulus';
// echarts is loaded via CDN <script> tag in base.html.twig — available globally


export default class extends Controller {
    static targets = ['entity', 'metric', 'chart'];

    static values = {
        utpMetrics: Object,
        uatpMetrics: Object, 
        seriesUrl: String
    };

    chart = null;

    connect() {
        //this.chart = echarts.init(this.chartTarget);
        this.chart = echarts.init(this.chartTarget, null, { renderer: 'svg' });

        this.populateMetrics();
        this.refresh();
        window.addEventListener('resize', this.handleResize);
    }

    disconnect() {
        window.removeEventListener('resize', this.handleResize);
        this.chart?.dispose();
    }

    handleResize = () => {
        this.chart?.resize();
    }

    refresh() {
        const entityType = this.currentEntityType();
        const currentMetricOptions = Array.from(this.metricTarget.options).map(o => o.value);
        const expectedMetrics = entityType === 'utp' ? this.utpMetricsValue : this.uatpMetricsValue;
        const expectedKeys = Object.keys(expectedMetrics);

        const sameSet = 
            currentMetricOptions.length === expectedKeys.length && 
            currentMetricOptions.every(k => expectedKeys.includes(k));

        if(!sameSet) {
            this.populateMetrics();
        }

        this.fetchAndRender();
    }

    populateMetrics() {
        const entityType = this.currentEntityType();
        const metrics = entityType === 'utp' ? this.utpMetricsValue : this.uatpMetricsValue;

        this.metricTarget.innerHTML = '';
        for(const [key, label] of Object.entries(metrics)){
            const opt = document.createElement('option');
            opt.value = key;
            opt.textContent = label;
            this.metricTarget.appendChild(opt);
        }
    }

    currentEntityType() {
        const [type] = this.entityTarget.value.split(':');
        return type;
    }

    currentEntityId() {
        const [, id] = this.entityTarget.value.split(':');
        return id;
    }

    async fetchAndRender() {
        const entityType = this.currentEntityType();
        const entityId = this.currentEntityId();
        const metric = this.metricTarget.value;

        if(!entityType || !entityId || !metric){
            return;
        }

        const url = `${this.seriesUrlValue}?entityType=${entityType}&entityId=${entityId}&metric=${encodeURIComponent(metric)}`;

        const res = await fetch(url);
        if(!res.ok) {
            console.error('Failed to fetch series:', res.status);
            return;
        }
        const data = await res.json();

        this.chart.setOption({
            title: { text: data.label },
            tooltip: { trigger: 'axis' },
            xAxis:   { type: 'category', data: data.dates },
            yAxis:   { type: 'value' },
            series:  [{
                type: 'line',
                data: data.values,
                showSymbol: false,
            }],
        }, true);

    }
}