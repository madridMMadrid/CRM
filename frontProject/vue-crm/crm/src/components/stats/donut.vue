<template>
    <div class="panel panel-flat">
        <div class="panel-body">
          <div class="media-left">
            <div class="chart-container text-center">
              <div class="display-inline-block" ref="chart"></div>
            </div>
          </div>
          <div class="media-left">
            <div class="table-responsive">
              <table class="table text-nowrap">
                <tbody>
                  <tr v-for="(item, index) in items">
                    <td class="no-border-top">{{ item.name }}</td>
                    <td class="no-border-top">{{ item.value }}</td>
                    <td class="no-border-top">
                      <small class="text-success text-size-base"><i class="icon-arrow-up12"></i> 
                        ({{ Math.round(item.difference) }}%)
                      </small>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
</template>

<script>
export default {
  name: 'StatsDonut',
  props: {
    items: {}
  },

  data() {
    return {
      donut: []
    };
  },

  mounted: function(){
     var _self = this;

     var donutObj = []

     for (var i = 0; i < Object.keys(_self.items).length; i++) {
      var name = Object.keys(_self.items)[i],
          item = _self.items[name];

      donutObj.push({
        browser: item.name,
        icon: "<i class='"+item.icon+" position-left'></i>",
        value: item.value,
        color: item.color
      })
     }

     _self.donut = donutObj;
  },

  methods: {
    drawChart: function(data, size){
        // Add data set
        var element = this.$refs.chart;

        $(element).empty();

        // Main variables
        var d3Container = d3.select(element),
            distance = 2, // reserve 2px space for mouseover arc moving
            radius = (size/2) - distance,
            sum = d3.sum(data, function(d) { return d.value; });

        console.log('sum:', sum, 'data:', data)

        // Tooltip
        // ------------------------------

        var tip = d3.tip()
            .attr('class', 'd3-tip')
            .offset([-10, 0])
            .direction('e')
            .html(function (d) {
                return "<ul class='list-unstyled mb-5'>" +
                    "<li>" + "<div class='text-size-base mb-5 mt-5'>" + d.data.icon + d.data.browser + "</div>" + "</li>" +
                    "<li>" + "Сумма: &nbsp;" + "<span class='text-semibold pull-right'>" + d.value + "</span>" + "</li>" +
                    "<li>" + "Процент: &nbsp;" + "<span class='text-semibold pull-right'>" + (100 / (sum / d.value)).toFixed(2) + "%" + "</span>" + "</li>" +
                "</ul>";
            })



        // Create chart
        // ------------------------------

        // Add svg element
        var container = d3Container.append("svg").call(tip);
        
        // Add SVG group
        var svg = container
            .attr("width", size)
            .attr("height", size)
            .append("g")
                .attr("transform", "translate(" + (size / 2) + "," + (size / 2) + ")");  



        // Construct chart layout
        // ------------------------------

        // Pie
        var pie = d3.layout.pie()
            .sort(null)
            .startAngle(Math.PI)
            .endAngle(3 * Math.PI)
            .value(function (d) { 
                return d.value;
            }); 

        // Arc
        var arc = d3.svg.arc()
            .outerRadius(radius)
            .innerRadius(radius / 2);



        //
        // Append chart elements
        //

        // Group chart elements
        var arcGroup = svg.selectAll(".d3-arc")
            .data(pie(data))
            .enter()
            .append("g") 
                .attr("class", "d3-arc")
                .style('stroke', '#fff')
                .style('cursor', 'pointer');
        
        // Append path
        var arcPath = arcGroup
            .append("path")
            .style("fill", function (d) { return d.data.color; });

        // Add tooltip
        arcPath.on('mouseover', function (d, i) {

                // Transition on mouseover
                d3.select(this)
                .transition()
                    .duration(500)
                    .ease('elastic')
                    .attr('transform', function (d) {
                        d.midAngle = ((d.endAngle - d.startAngle) / 2) + d.startAngle;
                        var x = Math.sin(d.midAngle) * distance;
                        var y = -Math.cos(d.midAngle) * distance;
                        return 'translate(' + x + ',' + y + ')';
                    });
            }).on("mousemove", function (d) {
                
                // Show tooltip on mousemove
                tip.show(d)
                    .style("top", (d3.event.pageY - 40) + "px")
                    .style("left", (d3.event.pageX + 30) + "px");
            }).on('mouseout', function (d, i) {

                // Mouseout transition
                d3.select(this)
                .transition()
                    .duration(500)
                    .ease('bounce')
                    .attr('transform', 'translate(0,0)');

                // Hide tooltip
                tip.hide(d);
            });

        // Animate chart on load
        arcPath.transition()
                .delay(function(d, i) { return i * 500; })
                .duration(500)
                .attrTween("d", function(d) {
                    var interpolate = d3.interpolate(d.startAngle,d.endAngle);
                    return function(t) {
                        d.endAngle = interpolate(t);
                        return arc(d);  
                    }; 
                });
    }
  },

  watch: {
    donut: function(val){
      var _self = this;
      _self.drawChart(val, 180);
    }
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>

</style>
