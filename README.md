# BlackJack
BlackJack Project

To build this game I used PHP to write the backend, and angular framework to write the frontend.
This game includes rest API, self made cookies engine, MVC design pattern in the backend, etc.. 

The game support two players and dealer:
One player will be you, other one will be Virtual player and dealer which is virtual as well.


Notice: Because I have worked with apache server using xampp I will explain the installation as I did,

To run the application you will need: 
1. clone the files into 'htdocs' folder in the xampp. Move the files that are inside 'htdocs/blackjack' to 'htdocs'.
2. in you'r 'hosts' file, add this line: 127.0.0.1		blackjack.com
3. set you'r SQL using the .sql file.
4. run the apache and sql server using xampp.
5. navigate to the 'blackjack' folder in the 'htdocs' using any cmd, and write:

    'npm install --save-dev @angular-devkit/build-angular' to install @angular-devkit/build-angular as dev dependency.
    
    'npm serve' to run the angular.
    
    (Next time you run the application just type 'npm start' instead.)
6. enter you'r browser and write 127.0.0.1:4200 in the url line.

That's all!
