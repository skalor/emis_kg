import {StatisticsCountGender} from "./StatisticsCountGender.js";

let json = {
  "_defaultColors": false,
  "chart": {
    "type": "column",
    "borderWidth": 1
  },
  "xAxis": {
    "title": {
      "text": "Годы"
    },
    "categories": [
      "2019-2020",
      "2019-2020",
      "2020-2021"
    ]
  },
  "yAxis": {
    "title": {
      "text": "Общее число"
    }
  },
  "title": {
    "text": " Количество учащихся в год"
  },
  "tooltip": {
    "useHTML": true
  },
  "legend": {
    "useHTML": true
  },
  "series": [
    {
      "name": "Мужской",
      "data": [
        1,
        300,
        0
      ]
    },
    {
      "name": "Женский",
      "data": [
        0,
        288,
        0
      ]
    },
    {
      "name": "Общее число",
      "data": [
        1,
        588,
        0
      ]
    }
  ],
  "credits": {
    "enabled": false
  },
  "colors": [
    "#7D68D5",
    "#F1658D",
    "#FFD16A"
  ]
}

let graf = new StatisticsCountGender(".cl", json, 1, 1000)
graf.render();
graf.work();

