import { Component } from '@angular/core';
import {BackendService} from "./services/backend.service";
import {Router} from '@angular/router';
import {AuthService} from "./services/auth.service";
import Swal from 'sweetalert2/dist/sweetalert2.all.js';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
})
export class AppComponent{
  title = 'BlackJack';
  constructor(
    public backend_service: BackendService,
    public router: Router,
    public auth_service: AuthService,
  ) {}
  // Swal element
  public swal = Swal;
}
