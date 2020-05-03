import {Component} from '@angular/core';
import {AppComponent} from "../../../app.component";
import {Participant} from "../../../entities/participant";
import {dealer, hand, player, virtual} from "../../../constants/contant";

@Component({
  selector: 'board',
  templateUrl: './board.component.html',
  styleUrls: ['./board.component.css'],
})
export class BoardComponent extends AppComponent {
  // The players at the game
  public players = [];
  // Board id
  public board_id;

  ngOnInit(){
    // Set the new users
    this.players[player.user] = new Participant(); // Describes user player
    this.players[player.virtual] = new Participant(); // Describes virtual player
    this.players[player.dealer] = new Participant(); // Describes dealer

    // Get the user full name and email
    this.backend_service.current_user.subscribe((user:any) => {
      // Set the players properties
      this.players[player.user].full_name = user.full_name;
      this.players[player.user].set_id(user.user_id);
      this.players[player.virtual].set_id(virtual.id);
      this.players[player.dealer].set_id(dealer.id);
    });
    console.log('user_data',this.players[player.user].full_name);
  }

  /**
   * When user choose entrance fee this method will be activate.
   */
  public entranceFeeEntered() {
    // Gets the entrance fee
    let fee = (<HTMLInputElement>document.getElementById('entrance_fee_input'));

    // Validate the data
    if (fee.checkValidity()) {
      // Send a request to the backend to initial new board game
      let result = this.backend_service.initialBoard({'user_id': this.players[player.user].id, 'user_bet': fee.value});

      // Check the result
      if( result['success'] ) {
        // The data is valid, disable the option to enter entrance fee again
        let entrance_fee_form = (<HTMLInputElement>document.getElementById('entrance_fee_form'));
        entrance_fee_form.style.display = "none";

        // Show the play buttons
        let playing_buttons = (<HTMLInputElement>document.getElementById('playing_buttons'));
        playing_buttons.style.display = "block";

        // Save the board ID
        this.board_id = result.board_id;

        // Call play
        this.play(result);
      } else {
        this.swal.fire({
          icon: 'error',
          title: 'Cannot choose this initial bet',
          text: 'Too low balance',
        })
      }
    }
  }

  /**
   * User choose to `HIT` - means user wants another card.
   * We will calculate virtual player and dealer if need so as well.
   */
  public hit(){
    // Can the user keep play?
    if ( this.players[player.user].get_status(0) !== "0" && this.players[player.user].get_status(1) !== "0" ) {
      // He can't, alert
      this.swal.fire({
        icon: 'error',
        title: 'Wait for the game to end',
        text: 'You have reached more then 21 or finished the turn',
      })
    } else {
      // Try Gets another card from the backend
      let response = this.backend_service.hit({'board_id': this.board_id, 'user_id': this.players[player.user].id});

      // Alert if user cannot hit
      if (!response['success']) {
        this.swal.fire({
          icon: 'error',
          title: 'Attention',
          text: 'Cannot HIT',
        })
      } else {
        /* User successfully `HIT` */
        // Restart the animation
        var el = document.getElementById('player_back');
        el.style.animation = 'none';
        el.offsetHeight; /* trigger reflow */
        el.style.animation = null;

        // Play the turn
        this.play(response);
      }
    }
    // Try to disable buttons if the player have finished hes turn
    this.disableButtons();
  }

  /**
   * User choose to `STAND`
   * We will calculate virtual player and dealer if need so as well.
   */
  public stand(){
    // Can the user keep play?
    if ( this.players[player.user].get_status(0) !== "0" && this.players[player.user].get_status(1) !== "0" ) {
      // He can't, alert
      this.swal.fire({
        icon: 'error',
        title: 'Wait for the game to end',
        text: 'You have reached more then 21 or finished the turn',
      });
    } else {
      // Try Gets another card from the backend
      let response = this.backend_service.stand({'board_id': this.board_id, 'user_id': this.players[player.user].id});

      // Alert if user cannot stand
      if (!response['success']) {
        this.swal.fire({
          icon: 'error',
          title: 'Attention',
          text: 'Cannot STAND',
        })
      } else {
        // Play the turn
        this.play(response);
      }
    }
    // Try to disable buttons if the player have finished hes turn
    this.disableButtons();
  }

  /**
   * User choose to `SPLIT`
   * We will calculate virtual player and dealer if need so as well.
   */
  public split(){
    // Can the user keep play?
    if ( this.players[player.user].get_status(0) !== "0" && this.players[player.user].get_status(1) !== "0" ) {
      // He can't, alert
      this.swal.fire({
        icon: 'error',
        title: 'Wait for the game to end',
        text: 'You have reached more then 21 or finished the turn',
      })
    } else {
      // Try Gets another card from the backend
      let response = this.backend_service.split({'board_id': this.board_id, 'user_id': this.players[player.user].id});

      // Alert if user cannot hit
      if (!response['success']) {
        this.swal.fire({
          icon: 'error',
          title: 'Attention',
          text: 'Cannot SPLIT',
        })
      } else {
        /* User successfully `SPLIT` */
        // Restart the animations
        // Card animation
        var el = document.getElementById('player_back');
        el.style.animation = 'none';
        el.offsetHeight; /* trigger reflow */
        el.style.animation = null;

        // Money animation
        var el = document.getElementById('player_chips');
        el.style.animation = 'none';
        el.offsetHeight; /* trigger reflow */
        el.style.animation = null;

        // Let the player know where hes turn start at
        this.swal.fire({
          position: 'top-end',
          icon: 'info',
          title: 'Successfully split',
          text: 'The turn will start from the top hand, Good luck!',
        })

        // Reset the player hand
        this.players[player.user].reset_hand();
        // Play the turn
        this.play(response);
      }
    }
    // Try to disable buttons if the player have finished hes turn
    this.disableButtons();
  }

  /**
   * User choose to `DOUBLE`
   * We will calculate virtual player and dealer if need so as well.
   */
  public double(){
    // Can the user keep play?
    if ( this.players[player.user].get_status(0) !== "0" && this.players[player.user].get_status(1) !== "0" ) {
      // He can't, alert
      this.swal.fire({
        icon: 'error',
        title: 'Wait for the game to end',
        text: 'Cannot double at this part',
      })
    } else {
      // Try Gets another card from the backend
      let response = this.backend_service.double({'board_id': this.board_id, 'user_id': this.players[player.user].id});

      // Alert if user cannot hit
      if (!response['success']) {
        this.swal.fire({
          icon: 'error',
          title: 'Attention',
          text: 'Cannot DOUBLE',
        })
      } else {
        /* User successfully `HIT` */
        // Restart the animations
        // Card animation
        var el = document.getElementById('player_back');
        el.style.animation = 'none';
        el.offsetHeight; /* trigger reflow */
        el.style.animation = null;

        // Money animation
        var el = document.getElementById('player_chips');
        el.style.animation = 'none';
        el.offsetHeight; /* trigger reflow */
        el.style.animation = null;

        // Play the turn
        this.play(response);
      }
    }
    // Try to disable buttons if the player have finished hes turn
    this.disableButtons();
  }

  /**
   * User choose to `SURRENDER`
   * We will calculate virtual player and dealer if need so as well.
   */
  public surrender(){
    // Can the user keep play?
    if ( this.players[player.user].get_status(0) !== "0" && this.players[player.user].get_status(1) !== "0" ) {
      // He can't, alert
      this.swal.fire({
        icon: 'error',
        title: 'Wait for the game to end',
        text: 'Cannot surrender at this part',
      })
    } else {
      // Try Gets another card from the backend
      var response = this.backend_service.surrender({'board_id': this.board_id, 'user_id': this.players[player.user].id});

      // Alert if user cannot stand
      if (!response['success']) {
        this.swal.fire({
          icon: 'error',
          title: 'Attention',
          text: 'Cannot SURRENDER',
        });
      } else {
        // Otherwise, we have the data, update the user status
        // Play the turn
        this.play(response);
      }
    }
    // Try to disable buttons if the player have finished hes turn
    this.disableButtons();
  }

  /**
   * This method will be called in every move that the player is doing.
   * @param data  The data of the current board.
   *              This data will includes players information, and board information.
   */
  public async play(data) {
    // Delay function
    function wait_a_second() {
      return new Promise( resolve => setTimeout(resolve, 650) );
    }

    // Initial some variables
    let player_hand;

    // Loop through the players in the board
    for( let i = 0; i < this.players.length; i++ ) {
      // Loop through the new players data from the backend
      for (let j = 0; j < data['player'].length; j++) {

        // Make sure we are checking the right player
        if( this.players[i].id === data['player'][j].user_id ) {
          // Split the data for the players in case of split
          data['player'][j].hand = data['player'][j].hand.toString().split("||");
          data['player'][j].bet = data['player'][j].bet.toString().split("||");
          data['player'][j].status = data['player'][j].status.toString().split("||");

          // Set hes status
          this.players[i].set_status(data['player'][j].status);

          /* It is the player, check if we have for this player a new cards for each hand */
          // Loop through the hands (also bets)
          for (let hand_index = 0; hand_index < data['player'][j].hand.length; hand_index++) {

            // This check will happened for each hand
            if (this.players[i].get_hand(hand_index).length < data['player'][j].hand[hand_index].split("&").length) {

              // There is new cards, get them
              player_hand = this.players[i].getHandCards(data['player'][j].hand[hand_index].split("&"), hand_index);
              await wait_a_second();
              this.players[i].addCardsToBoard(player_hand, hand_index);
            }

            /* It is the player, check if we have for this player a new bets for each hand */
            // This check will happened for each bet
            if (this.players[i].bet[hand_index] !== data['player'][j].bet[hand_index]) {

              /* Add the chips to the board */
              // Does player have bet?
              if (this.players[i].bet[hand_index] === 0) {
                // Save the bet
                this.players[i].bet[hand_index] = parseInt(String(data['player'][j].bet[hand_index]));
                await wait_a_second();
                // He doesn't, we need to init chips
                this.players[i].addChipsToBoard(data['player'][j].bet[hand_index], true);
              } else {
                // Otherwise, we just update the amount of bet
                // Save the bet
                this.players[i].bet[hand_index] = parseInt(String(data['player'][j].bet[hand_index]));
                await wait_a_second();
                this.players[i].addChipsToBoard(data['player'][j].bet[hand_index]);
              }
            }
          }
          break;
        }
      }
    }

    // Check if we have finished the game
    if( typeof data['user'] !== 'undefined' ) {
      let finished_text = "You have own " + data['user']['own'] + ", and lost " + data['user']['lost'] +
        ". You'r new balance: " + data['user']['balance'] + " (Reward: " +data['user']['reward']+")";
      // We have finished the game
      this.swal.fire({
        position: 'top',
        title: 'Game have been finished!',
        width: 600,
        text: finished_text,
        backdrop: `
    rgba(0, 0, 0,0.4)
    url("../../../../assets/images/game/game_table/end.gif")
    top
    no-repeat`
      }).then(() => {
        // Restart the game
        this.resetComponent();
      })
    }
  }

  /**
   * THIS METHOD WILL HAPPENED WHEN USER WILL FINISH HES TURN!
   * The method will call the backend to play virtual player turn,
   * Also dealer turn and finish the game.
   */
  public virtual_play(){
    /* Check if we needs to play the virtual player and dealer turns */
    let check = true;
    // Loop through the player hands
    for( let i = 0; i < this.players[player.user].hand.length; i++ ) {
      // Foreach hand, check if there is cards on this hands
      if( this.players[player.user].hand[i].length > 0 ) {
        // There is cards, check the hand status
        if( this.players[player.user].status[i] === "0" ){
          // Player still playing
          check = false;
          break;
        }
      }
    }

    // Does the player finished hes turn?
    if( check ) {
      // He is, play the virtual player and dealer turns and finished the game
      let result = this.backend_service.playVirtualTurns({'board_id': this.board_id, 'user_id': this.players[player.user].id});
      // Restart the animations for the virtual player and the dealer
      var el = document.getElementById('virtual_back');
      el.style.animation = 'none';
      el.offsetHeight; /* trigger reflow */
      el.style.animation = null;

      var el = document.getElementById('dealer_back');
      el.style.animation = 'none';
      el.offsetHeight; /* trigger reflow */
      el.style.animation = null;

      // Money animation restart for the virtual player
      var el = document.getElementById('virtual_chips');
      el.style.animation = 'none';
      el.offsetHeight; /* trigger reflow */
      el.style.animation = null;

      // Play the virtual player and dealer
      this.play(result);
    }
  }

  /**
   * Disable the buttons if it possible.
   */
  private async disableButtons(){
    /* Check if the user have finished hes turn */
    if ( this.players[player.user].get_status(0) === "0" || this.players[player.user].get_status(1) === "0" ) {
      return;
    }

    // Gets the element
    var x = document.getElementsByClassName("btn_with_logo");
    // Loop through the elements and disable them
    for (let i = 0; i < x.length; i++) {
      (<HTMLElement>x[i]).setAttribute("disabled", "disabled");
    }
    let timerInterval
    this.swal.fire({
      position: 'top',
      title: "Other players are playing...",
      html: "<b></b>",
      timer: 2000,
      timerProgressBar: true,
      showCancelButton: false,
      showConfirmButton: false,
      onClose: () => {
        clearInterval(timerInterval)
      }
    })
    // Play the virtual player and dealer turns
    this.virtual_play();
  }

  /**
   * Reset component after finishing game
   */
  private resetComponent(){
    // Reset the players
    this.players[player['user']].reset();
    this.players[player['virtual']].reset();
    this.players[player['dealer']].reset();

    // Reset the `input` value of initial bet
    let input = (<HTMLInputElement>document.getElementById('entrance_fee_input'));
    input.value = "";

    // Gets the element
    var x = document.getElementsByClassName("btn_with_logo");
    // Loop through the elements and disable them
    for (let i = 0; i < x.length; i++) {
      (<HTMLElement>x[i]).removeAttribute("disabled");
    }

    // The data is valid, disable the option to enter entrance fee again
    let playing_buttons = (<HTMLInputElement>document.getElementById('playing_buttons'));
    playing_buttons.style.display = "none";

    // Show the play buttons
    let entrance_fee_form = (<HTMLInputElement>document.getElementById('entrance_fee_form'));
    entrance_fee_form.style.display = "block";
  }
}
