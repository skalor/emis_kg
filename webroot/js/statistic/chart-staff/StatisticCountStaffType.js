class StatisticCountStaffType
{
  constructor(blockName, json, speedAnimate)
  {
    this.html = $(blockName);
    this.object = json;
    this.speedAnimate = speedAnimate > 300 ? speedAnimate : 300;
  }

  work()
  {
    const infoText = this.html.find("#infoText");
    const dateText = this.html.find("#dateText");
    const titleText = this.html.find("#titleText");
    const noTeacherText = this.html.find("#noTeacherText");
    const teacherText = this.html.find("#teacherText");

    const noTeacherNum1 = this.html.find("#noTeacherNum1");
    const noTeacherNum2 = this.html.find("#noTeacherNum2");
    const noTeacherNum3 = this.html.find("#noTeacherNum3");
    const teacherNum1 = this.html.find("#teacherNum1");
    const teacherNum2 = this.html.find("#teacherNum2");
    const teacherNum3 = this.html.find("#teacherNum3");

    const noTeacherHeight1 = this.html.find("#noTeacherHeight1");
    const noTeacherHeight2 = this.html.find("#noTeacherHeight2");
    const noTeacherHeight3 = this.html.find("#noTeacherHeight3");
    const teacherHeight1 = this.html.find("#teacherHeight1");
    const teacherHeight2 = this.html.find("#teacherHeight2");
    const teacherHeight3 = this.html.find("#teacherHeight3");



    infoText.text(this.object.title.text);
    dateText.text(this.object.subtitle.text);
    titleText.text(this.object.xAxis.title.text);
    noTeacherText.text(this.object.xAxis.categories[0]);
    teacherText.text(this.object.xAxis.categories[1]);

    let boyCount = this.object.series[0].data;
    let girlCount = this.object.series[1].data;
    let allCount = this.object.series[2].data;
    let maxPr = allCount[0] > allCount[1] ? allCount[0] : allCount[1];
    maxPr = maxPr + maxPr/3;

    this.height(noTeacherHeight1,noTeacherNum1, 100 * boyCount[0] / maxPr);
    this.height(noTeacherHeight2,noTeacherNum2, 100 * girlCount[0] / maxPr);
    this.height(noTeacherHeight3,noTeacherNum3, 100 * allCount[0] / maxPr);
    this.height(teacherHeight1,teacherNum1, 100 * boyCount[1] / maxPr);
    this.height(teacherHeight2,teacherNum2, 100 * girlCount[1] / maxPr);
    this.height(teacherHeight3,teacherNum3, 100 * allCount[1] / maxPr);

    this.number(noTeacherNum1,boyCount[0]);
    this.number(noTeacherNum2,girlCount[0]);
    this.number(noTeacherNum3,allCount[0]);
    this.number(teacherNum1,boyCount[1]);
    this.number(teacherNum2,girlCount[1]);
    this.number(teacherNum3,allCount[1]);
  }

  number(num,count)
  {

    num[0].innerHTML = 0;
    let speed = 1;
    let speedPerTick = this.speedAnimate / count;
    if (speedPerTick < 4)
    {
      let group =  4 / speedPerTick;
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
      }, speedPerTick);
  }

  height(body,num,percent)
  {
    let height = body.attr("height");
    let y = body.attr("y");
    let posNum = num.attr("y");

    let maxHeight = 200;
    let minY = 107;
    let minPosNum = 81;

    let tiks = maxHeight * percent / 100;
    let i = 0;

    let speedPerTick = this.speedAnimate / (maxHeight * percent / 100);
    let speed = 2;
    if (speedPerTick < 4)
    {
      let group =  4 / speedPerTick;
      speed = Math.ceil(speed * group);
    }

    let interval = setInterval( () => {
      body.attr("height",height);
      body.attr("y",y);
      num.attr("y",posNum);
      for (let j = 0; j < speed; j++)
      {
        if (height <= maxHeight && y >= minY && i < tiks)
        {
          y --;
          height ++;
          posNum  --;
          i ++;
        }
        else if (height > maxHeight && y < minY && i > tiks)
        {
          y = minY;
          height = maxHeight;
          posNum = minPosNum;
          i = tiks;
        }
        else
        {
          clearInterval(interval);
        }
    }

    }, speedPerTick)
  }

  render()
  {
    this.html.html( `
    <svg width="421" height="359" viewBox="0 0 421 359" fill="none" xmlns="http://www.w3.org/2000/svg">
    <path d="M48 257H393" stroke="#AAAAAA"/>
    <path d="M48 307H393" stroke="#AAAAAA"/>
    <path d="M48 207H393" stroke="#AAAAAA"/>
    <path d="M48 157H393" stroke="#AAAAAA"/>
    <path d="M48 107H393" stroke="#AAAAAA"/>

    <text id="infoText"  x="30" y="30"  fill="#293845" font-family="Open Sans" font-size="16" font-weight="400" font-style ="normal" >
      Количество сотрудников по видам
    </text>
    <text id="dateText"  x="30" y="55"  fill="#999999" font-family="Open Sans" font-size="14" font-weight="400" font-style ="normal" >
      Для года 2019-2020
    </text>

    <path d="M36.7051 260.264C38.0996 260.264 39.1836 260.613 39.957 261.312C40.7305 262.008 41.1172 262.994 41.1172 264.271C41.1172 265.564 40.7344 266.559 39.9687 267.254C39.1992 267.945 38.1074 268.291 36.6934 268.291C35.2793 268.291 34.1934 267.943 33.4355 267.248C32.6777 266.549 32.2988 265.553 32.2988 264.26C32.2988 262.986 32.6836 262.002 33.4531 261.307C34.2227 260.611 35.3066 260.264 36.7051 260.264ZM36.7051 266.803C37.7598 266.803 38.5605 266.59 39.1074 266.164C39.6504 265.738 39.9219 265.107 39.9219 264.271C39.9219 263.439 39.6523 262.812 39.1133 262.391C38.5742 261.965 37.7715 261.752 36.7051 261.752C35.6543 261.752 34.8574 261.963 34.3145 262.385C33.7715 262.803 33.5 263.428 33.5 264.26C33.5 265.1 33.7715 265.734 34.3145 266.164C34.8574 266.59 35.6543 266.803 36.7051 266.803ZM37.1094 252.945C35.6641 252.945 34.5527 252.701 33.7754 252.213C32.998 251.721 32.498 250.939 32.2754 249.869C32.1113 249.061 31.9512 248.129 31.7949 247.074L33.0078 246.881C33.2031 248.162 33.3555 249.1 33.4648 249.693C33.5781 250.307 33.8301 250.75 34.2207 251.023C34.6113 251.293 35.1992 251.449 35.9844 251.492L35.9844 251.404C35.6602 251.182 35.4102 250.891 35.2344 250.531C35.0547 250.172 34.9648 249.787 34.9648 249.377C34.9648 248.58 35.2168 247.959 35.7207 247.514C36.2246 247.068 36.9316 246.846 37.8418 246.846C38.8848 246.846 39.6914 247.117 40.2617 247.66C40.832 248.199 41.1172 248.947 41.1172 249.904C41.1172 250.854 40.7656 251.598 40.0625 252.137C39.3594 252.676 38.375 252.945 37.1094 252.945ZM39.9922 249.811C39.9922 248.787 39.3203 248.275 37.9766 248.275C36.6797 248.275 36.0312 248.738 36.0312 249.664C36.0312 249.918 36.0859 250.164 36.1953 250.402C36.3047 250.641 36.4492 250.861 36.6289 251.064C36.8086 251.264 36.9941 251.418 37.1855 251.527C38.1074 251.527 38.8066 251.381 39.2832 251.088C39.7559 250.795 39.9922 250.369 39.9922 249.811ZM39.9336 230.076L39.9336 229.145L43.2793 229.145L43.2793 230.439L41 230.439L41 239.258L34.5195 239.258L34.5195 237.881L39.9102 237.881L39.9102 235.355L34.5195 235.355L34.5195 233.979L39.9102 233.979L39.9102 231.459L34.5195 231.459L34.5195 230.076L39.9336 230.076ZM41.1172 219.131C41.1172 220.139 40.8242 220.928 40.2383 221.498C39.6484 222.064 38.8379 222.348 37.8066 222.348C36.748 222.348 35.916 222.084 35.3105 221.557C34.7051 221.029 34.4023 220.305 34.4023 219.383C34.4023 218.527 34.6621 217.852 35.1816 217.355C35.7012 216.859 36.416 216.611 37.3262 216.611L38.0703 216.611L38.0703 220.93C38.6992 220.91 39.1836 220.74 39.5234 220.42C39.8594 220.1 40.0273 219.648 40.0273 219.066C40.0273 218.684 39.9922 218.328 39.9219 218C39.8477 217.668 39.7266 217.312 39.5586 216.934L40.6777 216.934C40.8379 217.27 40.9512 217.609 41.0176 217.953C41.084 218.297 41.1172 218.689 41.1172 219.131ZM35.4453 219.383C35.4453 219.82 35.584 220.172 35.8613 220.437C36.1387 220.699 36.543 220.855 37.0742 220.906L37.0742 217.965C36.5391 217.973 36.1348 218.102 35.8613 218.352C35.584 218.602 35.4453 218.945 35.4453 219.383ZM41.1172 206.217C41.1172 207.225 40.8242 208.014 40.2383 208.584C39.6484 209.15 38.8379 209.434 37.8066 209.434C36.748 209.434 35.916 209.17 35.3105 208.643C34.7051 208.115 34.4023 207.391 34.4023 206.469C34.4023 205.613 34.6621 204.937 35.1816 204.441C35.7012 203.945 36.416 203.697 37.3262 203.697L38.0703 203.697L38.0703 208.016C38.6992 207.996 39.1836 207.826 39.5234 207.506C39.8594 207.186 40.0273 206.734 40.0273 206.152C40.0273 205.77 39.9922 205.414 39.9219 205.086C39.8477 204.754 39.7266 204.398 39.5586 204.02L40.6777 204.02C40.8379 204.355 40.9512 204.695 41.0176 205.039C41.084 205.383 41.1172 205.775 41.1172 206.217ZM35.4453 206.469C35.4453 206.906 35.584 207.258 35.8613 207.523C36.1387 207.785 36.543 207.941 37.0742 207.992L37.0742 205.051C36.5391 205.059 36.1348 205.187 35.8613 205.437C35.584 205.687 35.4453 206.031 35.4453 206.469ZM34.5195 185.803L36.8867 185.803C37.5508 185.803 37.8828 185.459 37.8828 184.771C37.8828 184.439 37.8379 184.115 37.748 183.799C37.6543 183.482 37.5059 183.143 37.3027 182.779L34.5195 182.779L34.5195 181.402L41 181.402L41 182.779L38.2519 182.779C38.4863 183.158 38.6602 183.525 38.7734 183.881C38.8828 184.232 38.9375 184.617 38.9375 185.035C38.9375 185.711 38.7637 186.238 38.416 186.617C38.0644 186.996 37.5742 187.186 36.9453 187.186L34.5195 187.186L34.5195 185.803ZM34.5195 172.115L37.8418 172.115C38.2285 172.115 38.7969 172.146 39.5469 172.209L34.5195 168.957L34.5195 167.275L41 167.275L41 168.6L37.7363 168.6C37.5566 168.6 37.2656 168.59 36.8633 168.57C36.4609 168.547 36.168 168.527 35.9844 168.512L41 171.752L41 173.434L34.5195 173.434L34.5195 172.115ZM41.1172 156.699C41.1172 157.68 40.832 158.426 40.2617 158.937C39.6875 159.445 38.8652 159.699 37.7949 159.699C36.7051 159.699 35.8672 159.434 35.2812 158.902C34.6953 158.367 34.4023 157.596 34.4023 156.588C34.4023 155.904 34.5293 155.289 34.7832 154.742L35.8906 155.158C35.6641 155.74 35.5508 156.221 35.5508 156.6C35.5508 157.721 36.2949 158.281 37.7832 158.281C38.5098 158.281 39.0566 158.143 39.4238 157.865C39.7871 157.584 39.9687 157.174 39.9687 156.635C39.9687 156.021 39.8164 155.441 39.5117 154.895L40.7129 154.895C40.8574 155.141 40.9609 155.404 41.0234 155.686C41.0859 155.963 41.1172 156.301 41.1172 156.699ZM41 142.045L41 143.434L35.5977 143.434L35.5977 145.221C36.9883 145.33 38.0742 145.479 38.8555 145.666C39.6367 145.85 40.207 146.092 40.5664 146.393C40.9258 146.689 41.1055 147.076 41.1055 147.553C41.1055 147.854 41.0625 148.105 40.9766 148.309L39.8984 148.309C39.9531 148.164 39.9805 148.02 39.9805 147.875C39.9805 147.477 39.5332 147.164 38.6387 146.937C37.7402 146.707 36.3672 146.527 34.5195 146.398L34.5195 142.045L41 142.045ZM37.748 128.322C38.8066 128.322 39.6328 128.594 40.2266 129.137C40.8203 129.68 41.1172 130.436 41.1172 131.404C41.1172 132.01 40.9805 132.545 40.707 133.01C40.4336 133.475 40.041 133.832 39.5293 134.082C39.0176 134.332 38.4238 134.457 37.748 134.457C36.6973 134.457 35.8769 134.187 35.2871 133.648C34.6973 133.109 34.4023 132.35 34.4023 131.369C34.4023 130.432 34.7051 129.689 35.3105 129.143C35.9121 128.596 36.7246 128.322 37.748 128.322ZM37.748 133.039C39.2441 133.039 39.9922 132.486 39.9922 131.381C39.9922 130.287 39.2441 129.74 37.748 129.74C36.2676 129.74 35.5273 130.291 35.5273 131.393C35.5273 131.971 35.7187 132.391 36.1016 132.652C36.4844 132.91 37.0332 133.039 37.748 133.039Z" fill="#AAAAAA"/>
    <text id="titleText"  x="131" y="352"  fill="#AAAAAA" font-family="Open Sans" font-size="12" font-weight="600" font-style ="normal" letter-spacing = "0.5em" >
      Вид занятости
    </text>

    <!-- ============================================== NoTeacher ==================================================== -->
    <text id="noTeacherNum1" x="77" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#688C90" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>
    <text id="noTeacherNum2" x="127" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#90688C" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>
    <text id="noTeacherNum3" x="176" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#6F52ED" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>

    <rect id="noTeacherHeight1" x="58" y="305" width="40" height="0" fill="#688C90"/>
    <rect id="noTeacherHeight2" x="108" y="305" width="40" height="0" fill="#90688C"/>
    <rect id="noTeacherHeight3" x="158" y="305" width="40" height="0" fill="#6F52ED"/>

    <rect x="58" y="312" width="140" height="17" fill="#C0D0E0"/>
    <text id="noTeacherText" x="129" y="322" dominant-baseline="middle" text-anchor="middle"  fill="#FFFFFF" font-family="Open Sans" font-size="11" font-weight="600" font-style ="normal">
      Непреподовательский
    </text>
    <!-- ============================================================================================================= -->


    <!-- =============================================== Teacher ===================================================== -->
    <text id="teacherNum1" x="240" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#688C90" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>
    <text id="teacherNum2" x="290" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#90688C" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>
    <text id="teacherNum3" x="340" y="280" dominant-baseline="middle" text-anchor="middle"  fill="#6F52ED" font-family="Open Sans" font-size="36" font-weight="700" font-style ="normal">
      0
    </text>

    <rect id="teacherHeight1" x="222" y="305" width="40" height="0" fill="#688C90"/>
    <rect id="teacherHeight2" x="272" y="305" width="40" height="0" fill="#90688C"/>
    <rect id="teacherHeight3" x="322" y="305" width="40" height="0" fill="#6F52ED"/>

    <rect x="222" y="312" width="140" height="17" fill="#C0D0E0"/>
    <text id="teacherText" x="290" y="322" dominant-baseline="middle" text-anchor="middle"  fill="#FFFFFF" font-family="Open Sans" font-size="11" font-weight="600" font-style ="normal">
      Обучение
    </text>
    <!-- ============================================================================================================= -->

    <defs>
    <filter id="filter0_d" x="0" y="0" width="421" height="386" filterUnits="userSpaceOnUse" color-interpolation-filters="sRGB">
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
