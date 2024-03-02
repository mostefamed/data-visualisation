import { dataVisualization } from "./charts/data-visualization.js";

document.onreadystatechange = () => {

    // Load the charts script only if there is an element with id prefixed by myChart
    if (document.querySelector("[id^='myChart']")) {
        dataVisualization();
    }  
  }
  