import {card, chip, dealer, hand, player, status, user, virtual} from '../constants/contant';

/**
 * Class Participant.
 * This class will include the properties for each participant,
 * also methods to change the cards and money position for him.
 */
export class Participant {
  private id;
  public full_name;
  public chip_position; // Chip position will be described as [left,top]
  private hand = [[],[]]; // For each hand he will have cards.
  private status = ["0","-2"]; // For each hand he will have status.
  public bet = [0,0]; // For each hand he will have bet.
  public card_position = []; // Card position will be described as [[left,top],[left,top]]
  private card_index = 0; // The cards index for card ID

  /**
   * Reset the player hand,
   * WILL HAPPENED ONLY WHEN USER CHOOSE TO SPLIT
   */
  public reset_hand() {
    this.hand = [[],[]];
    // We wants to set for each player card and bet position
    // Does this player virtual player?
    if( this.id === virtual.id ) {
      // It is, set the positions for him.
      this.card_position[0] = [virtual.first_hand.card_position.left, virtual.first_hand.card_position.top];
      this.card_position[1] = [virtual.second_hand.card_position.left, virtual.second_hand.card_position.top];
      this.chip_position = [virtual.first_hand.chip_position.left, virtual.first_hand.chip_position.top];
    }
    // Does this player dealer player?
    else if( this.id === dealer.id ) {
      // It is, set the positions for him.
      this.card_position[0] = [dealer.first_hand.card_position.left, dealer.first_hand.card_position.top];
    }
    // Otherwise, its user
    else {
      // It is, set the positions for him.
      this.card_position[0] = [user.first_hand.card_position.left, user.first_hand.card_position.top];
      this.card_position[1] = [user.second_hand.card_position.left, user.second_hand.card_position.top];
      this.chip_position = [user.first_hand.chip_position.left, user.first_hand.chip_position.top];
    }
  }

  /**
   * Setter - Set a new ID for user.
   * This method will set the values of the cards and bets position on the board.
   * @param id int The ID itself.
   */
  public set_id(id) {
    // Set the ID
    this.id = id;

    // We wants to set for each player card and bet position
    // Does this player virtual player?
    if( id === virtual.id ) {
      // It is, set the positions for him.
      this.card_position[0] = [virtual.first_hand.card_position.left, virtual.first_hand.card_position.top];
      this.card_position[1] = [virtual.second_hand.card_position.left, virtual.second_hand.card_position.top];
      this.chip_position = [virtual.first_hand.chip_position.left, virtual.first_hand.chip_position.top];
    }
    // Does this player dealer player?
    else if( id === dealer.id ) {
      // It is, set the positions for him.
      this.card_position[0] = [dealer.first_hand.card_position.left, dealer.first_hand.card_position.top];
    }
    // Otherwise, its user
    else {
      // It is, set the positions for him.
      this.card_position[0] = [user.first_hand.card_position.left, user.first_hand.card_position.top];
      this.card_position[1] = [user.second_hand.card_position.left, user.second_hand.card_position.top];
      this.chip_position = [user.first_hand.chip_position.left, user.first_hand.chip_position.top];
    }
  }

  /**
   * Getter - Return the hand depend on index.
   * User might have two hands if he choose to split.
   * By default user will see the first hand.
   * @param index int   index of hand.
   * @return false||array return false in case of empty hand otherwise return the hand.
   */
  public get_hand(index){
    return this.hand[index];
  }

  /**
   * Getter - Return the status.
   * Player might have two statuses for each hand,
   * we will return the required status.
   * @param index int   index of status.
   */
  public get_status(index){
    return this.status[index];
  }

  /**
   * Setter - Set player's status.
   * This method will saves the data as two array.
   * Each array will describe status per hand.
   * @param data string  Gets the data from the backend as one string.
   */
  public set_status(data){
    // Do we have one status?
    if( data.length === 1 ) {
      this.status[status.first] = data[status.first];
    } else {
      // Otherwise, we have two statuses
      this.status[status.first] = data[status.first];
      this.status[status.second] = data[status.second];
    }
  }

  /**
   *  Adding card to the board.
   * @param current_hand  The current hand that has the cards we are adding to the board.
   * @param index         The current hand index (in case of split we will have two hands for one player).
   */
  public addCardsToBoard(current_hand, index ) {
    // Loop through the user hand cards
    for (let i = 0; i < current_hand.length; i++) {
      if( typeof current_hand[i] === 'undefined' ) continue;
      var elem = document.createElement("img");
      elem.setAttribute("src", "../../assets/images/game/cards_face/" + current_hand[i] + ".jpg");
      elem.style.position = "absolute";
      elem.style.height = card.height;
      elem.style.width = card.width;
      this.card_position[index][0] = (parseInt(this.card_position[index][0]) + 1).toString() + "%"; // Change the next card position
      elem.style.left = this.card_position[index][0];
      elem.style.top = this.card_position[index][1];
      elem.id = 'card'.concat((this.card_index).toString());
      this.card_index++;
      document.getElementById("table").appendChild(elem);
    }
  }

  /**
   *  Gets the new cards the user have.
   * @param current_hand  The current player's hand represent as number + shape.
   * @param hand_num      The current hand index (in case of split we will have two hands for one player).
   * @return array        The user current hand.
   */
  public getHandCards(current_hand, hand_num) {
    // Loop through the player cards in the hand
    for (let index in current_hand) {
      // Do we have this card already in the current player hand?
      if (!this.hand[hand_num].includes(current_hand[index])) {
        // We don't, add it to the player hand
        this.hand[hand_num].push(current_hand[index]);
      } else {
        // Otherwise, we have this card already,
        // We don't want to put it on the game table again,
        // So we just delete it from the current hand
        delete current_hand[index];
      }
    }
    // Loop through the finally cards we have in the current hand
    for (let index in current_hand) {
      // Remove the cards deck index
      current_hand[index] = current_hand[index].slice(0, -1);
    }

    // Make sure we return array
    if( !Array.isArray(current_hand) ) return [current_hand];

    // Return the hand
    return current_hand;
  }

  /**
   *  Adding chips to the board, depend on who is the player that we are adding a chips to.
   * @param bet     The amount of bet.
   * @param init    On init set the chips location, otherwise don't - just update the span.
   */
  private addChipsToBoard(bet, init = false) {
    // Add chips to the board only on init!
    if (init) {
      // If it's dealer we don't want to add chips picture
      if (this.id !== dealer.id) {
        var elem = document.createElement("img");
        elem.setAttribute("src", "../../../../assets/images/game/chips/chips.png");
        elem.style.position = "absolute";
        elem.style.height = chip.height;
        elem.style.width = chip.width;
        elem.style.left = this.chip_position[0];
        elem.style.top = this.chip_position[1];
        elem.id = 'chip'.concat((this.id).toString());
        document.getElementById("table").appendChild(elem);
      }
    }

    // Change the chips span in dependency on which player
    if (this.id === virtual.id) {
      let virtual_chips_span = (<HTMLInputElement>document.getElementById('virtual_chips_span'));
      virtual_chips_span.innerText = (+this.bet[0] + +this.bet[1]).toString();
    }
    else if (this.id === dealer.id) {
      let dealer_chips_span = (<HTMLInputElement>document.getElementById('dealer_chips_span'));
      dealer_chips_span.innerText = (+this.bet[0] + +this.bet[1]).toString();
    }
    else {
      let player_chips_span = (<HTMLInputElement>document.getElementById('player_chips_span'));
      player_chips_span.innerText = (+this.bet[0] + +this.bet[1]).toString();
    }
  }

  /**
   * Reset everything.
   */
  public reset() {
    // Loop through the cards
    for(let i = 0; i < this.card_index; i++ ) {
      // Remove each card
      (<HTMLInputElement>document.getElementById('card'.concat((i).toString()))).remove();
    }

    // Remove for players
    if (this.id !== dealer.id) {
      // Remove the chips
      (<HTMLInputElement>document.getElementById('chip'.concat((this.id).toString()))).remove();
    }

    // Change the chips span in dependency on which player
    if (this.id === virtual.id) {
      let virtual_chips_span = (<HTMLInputElement>document.getElementById('virtual_chips_span'));
      virtual_chips_span.innerText = '';
    }
    else if (this.id === dealer.id) {
      let dealer_chips_span = (<HTMLInputElement>document.getElementById('dealer_chips_span'));
      dealer_chips_span.innerText =  '';
    }
    else {
      let player_chips_span = (<HTMLInputElement>document.getElementById('player_chips_span'));
      player_chips_span.innerText =  '';
    }

    // Reset hand
    this.reset_hand();

    this.card_index = 0;
    this.status = ["0","-2"]; // For each hand he will have status.
    this.bet = [0,0]; // For each hand he will have bet.
  }
}
