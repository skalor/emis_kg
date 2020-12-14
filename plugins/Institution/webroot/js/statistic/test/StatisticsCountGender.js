export class StatisticsCountGender 
{
  constructor(blockName, json, currentVue, speedAnimate)
  {
    this.blockName = blockName;
    this.object = json;
    this.currentVue = currentVue;
    this.speedAnimate = speedAnimate > 100 ? speedAnimate : 100;
  }

  work() 
  {
    const infoText = $("#infoText");
    const dateText = $("#dateText");
    const allNumText = $("#allNumText")
    const boy_body = $("#boy__body");
    const boy_header = $("#boy__header");
    const boy_num = $("#boy__num");
    const boy_line = $("#boy__line");
    const boy_nameText = $("#boy__nameText");
    const allNum = $("#allNum");
    const girl_body = $("#girl__body");
    const girl_header = $("#girl__header");
    const girl_num = $("#girl__num");
    const girl_line = $("#girl__line");
    const girl_nameText = $("#girl__nameText");

    let boyCount = this.object.series[0].data[this.currentVue];
    let girlCount = this.object.series[1].data[this.currentVue];
    let allCount = this.object.series[2].data[this.currentVue];

    infoText[0].innerHTML = this.object.title.text;
    dateText[0].innerHTML = "Для года " + this.object.xAxis.categories[this.currentVue];
    boy_nameText[0].innerHTML = this.object.series[0].name;
    girl_nameText[0].innerHTML = this.object.series[1].name;
    allNumText[0].innerHTML = this.object.series[2].name;

    let boyPercent = Math.ceil(100 * boyCount / allCount);
    let girlPercent = Math.ceil(100 * girlCount / allCount);

    this.height(boy_body,boy_header, boy_num, boy_line, boy_nameText, boyPercent);
    this.height(girl_body,girl_header, girl_num, girl_line, girl_nameText, girlPercent);
    this.number(boy_num, boyCount);
    this.number(girl_num, girlCount);
    this.number(allNum, allCount);
  }

  number(num, count)
    {
      let speed;

      let speedPerIncrement = this.speedAnimate / count;
      speed = 1;
      if (speedPerIncrement < 10) 
      {
        let group =  10 / speedPerIncrement;
        speed = Math.ceil(speed * group);
      }
      num[0].innerHTML = 0;
      let interval = setInterval( () => 
      {
        if (count >= Number(num[0].innerHTML) + speed)
        {   
          num[0].innerHTML = Number(num[0].innerHTML) + speed;
        }
        else if (count > Number(num[0].innerHTML) && count < Number(num[0].innerHTML) + speed)
        {
          num[0].innerHTML = count;
        }
        else 
        {
          clearInterval(interval);
        }
      
      }, speedPerIncrement);
    }

    height(body, header, num, line, name, percent)
    {
      let currentHeightBody = body.attr("y");
      let currentHeightHeader = header.attr("y");
      let currentHeightNum = num.attr("y");
      let currentHeightName = name.attr("y");
      let currentHeightLine = line.attr("y1");

      let y = currentHeightBody;
      let maxHeightBody = currentHeightBody - body.attr("height");
      let maxHeightHeader = currentHeightHeader - header.attr("height");
      let fullHeightTik = y - (maxHeightBody - header.attr("height"));

      let tiks = y - (fullHeightTik * percent / 100);

      let speedPerTick = this.speedAnimate / (fullHeightTik * percent / 100);

      let interval = setInterval(() => {
        body.attr("y",currentHeightBody);
        header.attr("y",currentHeightHeader);
        num.attr("y",currentHeightNum);
        name.attr("y",currentHeightName);
        line.attr("y1",currentHeightLine);
        line.attr("y2",currentHeightLine);

        let speed = 1;

        if (speedPerTick < 10) 
        {
          let group =  10 / speedPerTick;
          speed = Math.ceil(speed * group);
        }

        if ( y > tiks && y > maxHeightBody)
        {
          currentHeightBody -=speed;
          currentHeightNum -=speed;
          currentHeightName -=speed;
          currentHeightLine-=speed;
          y-=speed;
        }
        else if ( y > tiks && y > maxHeightHeader)
        {
          currentHeightHeader -=speed;
          currentHeightNum -=speed;
          currentHeightName -=speed;
          currentHeightLine -=speed;
          y-=speed;
        }
        else 
        {
          clearInterval(interval);
        }
      },speedPerTick);
    }
  
  render() 
  {
    this.html = $(this.blockName);
    this.html.html( `
      <svg width="380" height="380" viewBox="0 0 416 359" fill="none" xmlns="http://www.w3.org/2000/svg">
      <!-- ================================ boy ========================================= -->
        <g>
          <text id="boy__num" x="155" y="267" dominant-baseline="middle" text-anchor="middle"  fill="#688C90" font-family="Open Sans" font-size="26" font-weight="700" font-style ="normal">
            0
          </text>
          <line id="boy__line" x1="125" y1="280" x2="185" y2="280" stroke-width="1" stroke="#1A4E87"/>
          <text id="boy__nameText" x="155" y="290" dominant-baseline="middle" text-anchor="middle"  fill="#1A4E87" font-family="Open Sans" font-size="12" font-weight="400" font-style ="normal">
            Мужской
          </text>  
        </g>
        <mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="46" y="132" width="70" height="148">
          <path d="M103.001 155.439C101.994 155.439 101.294 155.439 100.462 155.439C100.375 155.985 100.287 156.447 100.287 156.909C100.2 168.796 100.025 180.684 100.068 192.571C100.112 218.11 100.2 243.649 100.287 269.23C100.287 269.986 100.331 270.784 100.287 271.54C100.025 276.79 96.3922 280.067 90.8779 279.983C86.2826 279.941 82.4314 276.034 82.4314 271.204C82.3876 257.552 82.4314 243.901 82.4314 230.249C82.4314 222.352 82.4314 214.414 82.4314 206.517C82.4314 205.761 82.4314 205.004 82.4314 204.122C81.4685 204.122 80.6808 204.122 79.6742 204.122C79.6742 205.004 79.6742 205.845 79.6742 206.643C79.6742 227.981 79.6742 249.361 79.6742 270.7C79.6742 277.042 74.4225 281.201 68.4268 279.689C64.6193 278.723 62.2122 275.74 61.9934 271.624C61.9497 270.574 61.9497 269.524 61.9497 268.474C61.9497 231.803 61.9497 195.091 61.9497 158.421C61.9497 157.497 61.9497 156.615 61.9497 155.481C61.0744 155.439 60.3304 155.397 59.1925 155.313C59.1925 156.237 59.1925 157.035 59.1925 157.833C59.1925 170.855 59.2363 183.876 59.1487 196.898C59.1487 198.494 58.536 200.216 57.7045 201.602C56.2603 203.996 53.1968 204.878 50.4396 204.164C47.9888 203.492 46.0632 201.014 46.0195 198.284C45.9757 192.403 46.0195 186.522 46.0195 180.642C46.0195 170.771 46.0195 160.9 46.0195 151.028C46.0195 139.645 53.9408 132.042 65.7572 132C76.1731 132 86.6327 132.042 97.0486 132C104.532 131.958 109.828 135.319 113.329 141.451C115.08 144.56 116.042 147.878 115.999 151.491C115.911 161.446 115.955 171.359 115.955 181.314C115.955 186.816 115.955 192.319 115.955 197.822C115.955 202.022 113.416 204.458 109.259 204.458C105.889 204.416 103.044 201.476 103.044 197.864C103.001 184.548 103.044 171.275 103.044 157.959C103.001 157.203 103.001 156.447 103.001 155.439Z" fill="#F0F3F4"/>
        </mask>6
        <mask id="mask01" mask-type="alpha" maskUnits="userSpaceOnUse" x="46" y="50" width="70" height="148">
          <path d="M97 112.959C97 121.283 90.0963 128 81.5445 128C72.9483 128 66 121.324 66 113.041C66 104.758 72.8592 98.0414 81.4555 98.0002C89.9626 97.959 97 104.717 97 112.959Z" fill="#000"/>
        </mask>
    
        <g mask="url(#mask0)">
          <rect x="46" y="132" width="70" height="148" fill="#F0F3F4"/>
          <rect id = "boy__body" x="46" y="280" width="70" height="150" fill="#688C90"/>
        </g>
        <g  mask="url(#mask01)">
          <rect x="46" y="97" width="70" height="32" fill="#F0F3F4"/>
          <rect id = "boy__header" x="46" y="130" width="70" height="35" fill="#688C90"/>
        </g>
      <!-- ============================================================================== --> 
      <text id="allNum" x="210" y="320" dominant-baseline="middle" text-anchor="middle"  fill="#6F52ED" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal"> 0 </text> <!-- Сумма -->
      <!-- ================================ girl ======================================== --> 
        <g>
          <text id="girl__num" x="350" y="267" dominant-baseline="middle" text-anchor="middle"  fill="#90688C" font-family="Open Sans" font-size="26" font-weight="700" font-style ="normal">
            0
          </text>
          <line id="girl__line" x1="320" y1="280" x2="380" y2="280" stroke-width="1" stroke="#1A4E87"/>
          <text id="girl__nameText" x="350" y="290" dominant-baseline="middle" text-anchor="middle"  fill="#1A4E87" font-family="Open Sans" font-size="12" font-weight="400" font-style ="normal">
            Женский
          </text>  
        </g>
        <mask id="mask1" mask-type="alpha" maskUnits="userSpaceOnUse" x="225" y="133" width="80" height="147">
          <path d="M249.601 222.82C243.413 222.82 237.605 222.82 231.46 222.82C237.942 199.82 244.381 177.072 250.821 154.282C248.969 153.444 248.17 153.947 247.622 155.832C244.213 167.981 240.593 180.088 237.31 192.279C236.005 197.139 230.113 198.857 226.577 195.254C225.104 193.746 224.683 192.07 225.23 190.101C229.439 175.271 233.648 160.44 237.899 145.61C239.962 138.404 247.159 133.042 254.694 133C261.555 133 268.457 133 275.318 133C282.684 133 289.461 137.902 291.608 144.982C296.027 159.602 300.362 174.265 304.698 188.928C306.087 193.578 302.509 197.726 297.627 197.181C295.227 196.93 293.923 195.463 293.291 193.243C290.429 183.398 287.525 173.553 284.62 163.708C283.821 161.027 283.063 158.346 282.263 155.664C281.674 153.737 281.674 153.737 279.149 153.989C285.462 176.653 291.734 199.234 298.09 222.149C292.113 222.149 286.346 222.149 280.369 222.149C280.369 223.155 280.369 223.909 280.369 224.663C280.369 240.708 280.369 256.795 280.369 272.841C280.369 276.821 278.307 279.208 274.35 279.879C269.931 280.591 266.942 278.119 266.9 273.679C266.9 257.466 266.9 241.295 266.9 225.082C266.9 224.244 266.9 223.448 266.9 222.526C265.595 222.526 264.501 222.526 263.196 222.526C263.196 223.448 263.196 224.286 263.196 225.082C263.196 241.211 263.196 257.34 263.196 273.469C263.196 277.239 261.134 279.46 257.388 279.879C253.81 280.298 250.569 278.245 249.811 274.977C249.601 274.097 249.601 273.176 249.601 272.254C249.601 256.628 249.601 240.96 249.601 225.333C249.601 224.621 249.601 223.867 249.601 222.82Z" fill="#F4F0F3"/>
        </mask>
        <mask id="mask11" mask-type="alpha" maskUnits="userSpaceOnUse" x="225" y="98" width="80" height="147">
            <path d="M252 113.5C252 105.496 257.964 99.0846 265.398 99.0008C272.832 98.917 279.041 105.58 279 113.584C278.959 121.504 272.75 128.083 265.439 127.999C257.923 127.915 252 121.504 252 113.5Z" fill="#F4F0F3"/>
        </mask>
    
        <g mask="url(#mask1)">
          <rect x="225" y="98" width="80" height="182" fill="#F4F0F3"/>
          <rect id = "girl__body" x="225" y="280" width="80" height="150" fill="#90688C"/>
        </g>
        <g mask="url(#mask11)">
          <rect x="225" y="98" width="80" height="182" fill="#F4F0F3"/>
          <rect id = "girl__header" x="225" y="130" width="80" height="35" fill="#90688C"/>
        </g>
      <!-- ============================================================================== --> 
        <text id="infoText"  x="25" y="35"  fill="#293845" font-family="Open Sans" font-size="16" font-weight="400" font-style ="normal" >
          Количество обучающихся в год
        </text>
        <text id="dateText"  x="25" y="55"  fill="#999999" font-family="Open Sans" font-size="14" font-weight="400" font-style ="normal" >
          Для года 
        </text>
        <text id="allNumText"  x="210" y="347" dominant-baseline="middle" text-anchor="middle"  fill="#1A4E87" font-family="Open Sans" font-size="12" font-weight="400" font-style ="normal" >
          Общее число 
        </text>
        
        
        <path d="M174 335H248" stroke="#1A4E87"/>
        <defs>
        <filter id="filter0_d" x="0" y="0" width="416" height="386" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
        <feFlood flood-opacity="0" result="BackgroundImageFix"/>
        <feColorMatrix in="SourceAlpha" type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0"/>
        <feOffset dy="3"/>
        <feGaussianBlur stdDeviation="4"/>
        <feColorMatrix type="matrix" values="0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0.25 0"/>
        <feBlend mode="normal" in2="BackgroundImageFix" result="effect1_dropShadow"/>
        <feBlend mode="normal" in="SourceGraphic" in2="effect1_dropShadow" result="shape"/>
        </filter>
        </defs>
        </svg>` )
  }
}

