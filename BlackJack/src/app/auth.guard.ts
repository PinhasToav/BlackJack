import {CanActivate, ActivatedRouteSnapshot, RouterStateSnapshot, Router} from '@angular/router';
import {Injectable} from "@angular/core";
import {AuthService} from "./services/auth.service";
import {BackendService} from "./services/backend.service";
/**
 * This class will be an authentication guard,
 * The class will let us know that the user that tries to route is authenticated
 */
@Injectable()
export class AuthGuard implements CanActivate {
  constructor(
    private authService: AuthService,
    private backendService: BackendService,
    private router: Router
  ){ }

  /**
   * @inheritDoc
   */
  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): boolean {
    // Does the user authenticated?
    if( this.authService.isAuth() ) {
      // Check if the user needs to initiate
      let result = this.backendService.initUserIfNeed(this.authService.getLocalStorage());
      // If the cookie is not valid
      if ( !result ) {
        // Otherwise, navigate him to 'welcome'
        console.log('Not authenticated!');
        this.router.navigate(['welcome']);
        return false;
      } else {
        // Otherwise, authenticated successfully
        return true;
      }
    } else {
      // Otherwise, navigate him to 'welcome'
      console.log('Not authenticated!');
      this.router.navigate(['welcome']);
      return false;
    }
  }
}
