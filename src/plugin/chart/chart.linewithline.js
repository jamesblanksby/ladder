Chart.types.Line.extend({
    name: "LineWithLine",
    initialize: function () {
        Chart.types.Line.prototype.initialize.apply(this, arguments);
    },
    draw: function () {
        Chart.types.Line.prototype.draw.apply(this, arguments);

        this.chart.ctx.beginPath();
        this.chart.ctx.lineWidth = .2;
        this.chart.ctx.strokeStyle = '#a4a4a4';
        this.chart.ctx.setLineDash([5, 5]);
        this.chart.ctx.moveTo(37, 127.5);
        this.chart.ctx.lineTo(this.scale.calculateX(this.datasets[0].points.length), 127.5);
        this.chart.ctx.stroke();

        this.chart.ctx.setLineDash([]);
    }
});
