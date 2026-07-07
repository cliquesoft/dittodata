var pidMeter;
var intBlocks = 5;		// start at 1 less than total value (starting from 0)
var toDark = true;

function ProgressMeter(MaxValue, Interval) {
  var i,j,toDark2;
  var aryColors = new Array('#0087ea','#1c9eff','#53b6ff','#77c6ff','#9dd6ff','#cae9ff');		// Dark to light

  j = intBlocks;
  toDark2 = toDark;
  for (i=0; i<MaxValue; i++) {
    document.getElementById("progress"+i).style.backgroundColor = aryColors[j];
    if (j == MaxValue) { toDark2 = true; } else if (j == 0) { toDark2 = false; }
    if (toDark2 == true) { j--; } else { j++; }
  }
  document.getElementById("progress"+i).style.backgroundColor = aryColors[j];

  if ((intBlocks == MaxValue) || (intBlocks == 0)) { toDark = !toDark; }
  if (toDark == false) { intBlocks--; } else { intBlocks++; }

  pidMeter = setTimeout("ProgressMeter("+MaxValue+",150);", Interval);
}
