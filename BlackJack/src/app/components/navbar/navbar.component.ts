import { Component } from '@angular/core';
import {AppComponent} from "../../app.component";
import {dealer, player, virtual} from "../../constants/contant";
import {Participant} from "../../entities/participant";

@Component({
  selector: 'navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.css']
})
export class NavbarComponent extends AppComponent{
  private user_id;

  ngOnInit(){
    // Gets the user ID
    this.backend_service.current_user.subscribe((user:any) => {
      this.user_id = user.user_id;
    });
  }

  /**
   * Gets an information about the logged in user.
   * balance, wins, loses, virtual players wins.
   */
  public myAccount() {
    // Try Gets another card from the backend
    let response = this.backend_service.myAccount({'user_id': this.user_id});

    // If we can get the user data
    if( response['success'] ) {
      this.swal.fire({
        html:
          '<style>' +
          'body{' +
          'font-family: "Roboto", sans-serif;' +
          '}' +
          '</style>' +
          '<body>' +
          '<h1 style="color: #B22200;">My account</h1>' +
          '<div style="text-align: center">' +
          '<p><b><u>My balance</u></b></p>' +
          '<p>'+response['balance']+'</p>' +
          '<p><b><u>My statistics</u></b></p>' +
          '<p style="color:#00b359">Wins: '+response['user_win']+'/'+response['user_games']+'</p>' +
          '<p style="color:#b30000">Loses: '+response['user_lose']+'/'+response['user_games']+'</p>' +
          '<p><b><u>Virtual user statistics</u></b></p>' +
          '<p style="color:#00b359">Wins: '+response['virtual_win']+'/'+response['virtual_games']+'</p>' +
          '<p style="color:#b30000">Loses: '+response['virtual_lose']+'/'+response['virtual_games']+'</p>' +
          '</div></body>',
        showCloseButton: true,
        showConfirmButton: false,
        showClass: {
          popup: 'animated zoomInUp faster'
        },
        hideClass: {
          popup: 'animated zoomOutUp faster'
        },
        width: 1000,
      })
    }
  }

  /**
   * Show black jack rules
   */
  public rules(){
    this.swal.fire({
      html:
        '<style>' +
        'body{' +
        'font-family: "Roboto", sans-serif;' +
        '}' +
        '</style>' +
        '<body>' +
        '<h1 style="color: #B22200;">BlackJack Rules</h1>' +
        '<div style="text-align: left">' +
          '<p><b>General rules</b></p>' +
            '<p>In this application you will play against virtual player, and virtual dealer. ' +
            'Players are each dealt two cards face up, and the dealer is dealt one card face up.\n' +
            'Face cards (Jack, Queen, and King) are all worth ten. Aces can be worth one or eleven. A hand\'s value is the sum of the card values. Players are allowed to draw additional cards to improve their hands.\n' +
            'Once all the players have completed their hands, it is the dealer\'s turn. The dealer hand will not be completed if all players have either busted or received blackjacks. The dealer then takes cards one by one until the cards total up to 17 points - At 17 points or higher the dealer must stay. DRAW WILL COUNT AS WIN!' +
            '</p>' +
          '<p><b>Player decisions:\n</b></p>' +
            '<p><u>Hit:</u> Take another card from the dealer.\n</p>' +
            '<p><u>Stand:</u> Take no more cards.\n</p>' +
            '<p><u>Double down:</u> The player is allowed to increase the initial bet by up to 100% in exchange for committing to stand after receiving exactly one more card.\n</p>' +
            '<p><u>Split:</u> If the first two cards of a hand have the same value, the player can split them into two hands, by moving a second bet equal to the first into an area outside the betting box. The dealer separates the two cards and draws an additional card on each, placing one bet with each hand, the player then plays out the two separate hands in turn.\n</p>' +
            '<p><u>Surrender</u> (only available as first decision of a hand):\n When the player surrenders, the house takes half the player\'s bet and returns the other half to the player.\n</p>' +
          '</p>' +
          '</p></p></div></body>',
      showCloseButton: true,
      showConfirmButton: false,
      showClass: {
        popup: 'animated zoomInUp faster'
      },
      hideClass: {
        popup: 'animated zoomOutUp faster'
      },
      width: 1000,
    })
  }

  /**
   * Logout function.
   */
  public logout() {
    localStorage.clear();
    // Navigate to the next component
    this.router.navigate(['welcome']);
  }
}
