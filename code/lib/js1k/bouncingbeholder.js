canvas=document.body.children[0];
screen_height=time=150;
last_height=screen_width=canvas.width=800;
unit=dead=50;
heights=[];

// The abbreviation loop, initializing the variabled needed by the key-handlers on the side.
for(prop in context=canvas.getContext('2d'))
  context[prop[jump=speed_x=speed_y=0]+(prop[6]||'')]=context[prop];

setInterval(function(){
  if(dead)
    // initialize the player position, score, and heightmap
    for(x=405,i=y=score=0;i<1e4;)
      // (screen_width is reused as the off-the-screen height of gap blocks)

      // a block can be a gap if its index is <9, or if the last block was no gap. after this test,
      // a random number is compared to .3 to determine whether an actual gap is generated, or a
      // regular random height.
      last_height=heights[i++]=
        i<9|last_height<screen_width&Math.random()<.3?screen_width:Math.random()*unit+80|0;

  // silly formula to create parabolic movement based on the time
  plant_pos=++time%99-unit;plant_pos=plant_pos*plant_pos/8+20;


  y+=speed_y;
  // only move horizontally if that doesn't take us deep underground (x/unit|0 fetches the index of
  // the block below an x coordinate)
  x+=y-heights[(x+speed_x)/unit|0]>9?0:speed_x;
  // compute final player height index, and ground level under it
  ground=heights[player_index=x/unit|0];
  // adjust y and speed_y based on whether we are on the ground or not
  speed_y=y<ground|speed_y<0?speed_y+1:(y=ground,jump?-10:0);

  // we'll need the context a lot
  with(context){
    A=function(color,x,y,radius){
      // a is the abbreviated form of arc
      radius&&a(x,y,radius,0,7,0);
      // if color is not a gradient object (we set a P property in gradient objects), it is an index
      // into a set of colors
      fillStyle=color.P?color:'#'+'ceff99ff78f86eeaaffffd45333'.substr(color*3,3);
      // f for fill, ba for beginPath
      fill(); ba();
    };

    // now loop over visible, or close to visible, blocks, and draw them and their clouds
    for(dead=i=0;i<21;i++){
      // this loop is reused for drawing the background/rainbow, which consists of seven concentric
      // circles. there's no good reason why interleaving clearing the screen with drawing the
      // screen's contents should work, but in this case it does
      i<7&&A(i%6,screen_width/2,235,i?250-15*i:screen_width);

      // we start drawing 5 units in front of the player (first four will be off-screen, needed just
      // for clouds)
      height_index=player_index-5+i;

      scroll_pos=x-height_index*unit;
      // since player screen position is fixed, we can use scroll position for collision detection.
      // this variable indicates whether the player is in the 'middle' of the current block
      player_in_middle=scroll_pos>9&scroll_pos<41;

      // ta for translate. move to start of block to make other drawing commands shorter
      ta(unit-scroll_pos,0);
      // cL for createLinearGradient, for the ground/grass gradient
      gradient=cL(0,height=heights[height_index],0,height+9);
      // if height is divisible by 6, there's a coin here. draw it. if the player is standing on the
      // ground, in the middle of this unit, pick up the coin
      height%6||(A(2,25,height-7,5),y^ground||player_in_middle&&(heights[height_index]-=.1,score++));

      // abbreviate, since we need this twice (and use it again to test whether a value passed to A
      // is a gradient)
      gradient.P=gradient.addColorStop;
      // this implements sinky terrain---when the index is divisible by 7, we use a different color,
      // and do the sinking if the player is standing here
      gradient.P(0,height_index%7?'#5e1':(height_index^player_index||y^height||
                                          (y=heights[height_index]+=plant_pos/99),'#a59'));
      // brown earth color for the bottom of the gradient
      gradient.P(1,'#b93');

      // this draws the clouds
      height_index%4&&A(6,time/2%200,9,height_index%2?27:33);

      // draws the terrain block. m is moveTo, qt is quadraticCurveTo, l is lineTo
      m(-6,screen_height);qt(-6,height,3,height);l(47,height);qt(56,height,56,screen_height);A(gradient);

      // draw deco trees or piranha plant (height==screen_width for gap blocks), check for collision
      // with plant
      height_index%3?0:height<screen_width
        ?(A(gradient,33,height-15,10),fc(31,height-7,4,9))
        :(A(7,25,plant_pos,9),A(3,25,plant_pos,5),fc(24,plant_pos,2,screen_height),
          dead=player_in_middle&y>plant_pos-9?1:dead);

      // undo block-local translation
      ta(scroll_pos-unit,0)
    }

    // draws the player, using the speed to adjust the position of the iris
    A(6,unit,y-9,11);
    A(5,iris_x=unit+speed_x*.7,iris_y=y-9+speed_y/5,8);
    A(8,iris_x,iris_y,5);

    // color is already dark from eye pupil, draw score with this color
    fx(score+'¢',5,15)
  }

  // check whether the player has fallen off the screen
  dead=y>screen_height?1:dead
},unit);

onkeydown=onkeyup=function(e){
  // if this is a keydown event, new_val gets the value 4, otherwise 0
  new_val=e.type[5]?4:0;
  e=e.keyCode;

  // give jump a truthy value if up was pressed, falsy if up was released
  jump=e^38?jump:new_val;

  // similar for speed_x, inverting new_val if left is pressed
  speed_x=e^37?e^39?speed_x:new_val:-new_val
}