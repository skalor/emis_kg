import {Component, NgModule, ViewChild} from '@angular/core';
import {BrowserModule} from '@angular/platform-browser';
import {FormControl, FormGroup, ReactiveFormsModule, FormsModule} from '@angular/forms';
import {NgSelectModule, NgOption} from '@ng-select/ng-select';

@Component({
    selector: 'my-app',
    template: `
        <h1>Angular ng-select <small class="text-muted"><a target="_blank" href="https://github.com/ng-select/ng-select">Open in Github</a></small></h1>
        <label>Your first ng-select</label>
        <ng-select [items]="cities"
                   bindLabel="name"
                   placeholder="Select city"
                   [(ngModel)]="selectedCity">
        </ng-select>
        <p>
            Selected city: {{selectedCity | json}}
        </p>
        <hr />
        
        <label>Multiselect with custom bindings</label>
        <ng-select [items]="cities2"
                   bindLabel="name"
                   bindValue="id"
                   [multiple]="true"
                   placeholder="Select cities"
                   [(ngModel)]="selectedCityIds">
        </ng-select>
        <p>
            Selected cities: {{selectedCityIds}}
        </p>
        <hr />
        
        <label>Custom tags</label>
        <ng-select [items]="users"
                   bindLabel="name"
                   bindValue="id"
                   [addTag]="addCustomUser"
                   [multiple]="true"
                   placeholder="Select user or add custom tag"
                   [(ngModel)]="selectedUserIds">
        </ng-select>
        <p>
            Selected user: {{selectedUserIds}}
        </p>
        <hr />
        
        <label>Custom templates</label>
        <ng-select [items]="cities3"
                   bindLabel="name"
                   bindValue="name"
                   placeholder="Select city"
                   [(ngModel)]="selectedCityName">
            <ng-template ng-header-tmp>
              Custom header
            </ng-template>
            <ng-template ng-label-tmp let-item="item">
                <img height="15" width="15" [src]="item.avatar"/>
                <b>{{item.name}}</b> is cool
            </ng-template>
            <ng-template ng-option-tmp let-item="item" let-index="index">
                <img height="15" width="15" [src]="item.avatar"/>
                <b>{{item.name}}</b>
            </ng-template>
            <ng-template ng-footer-tmp>
              Custom footer
            </ng-template>
        </ng-select>
        <p>
            Selected city: {{selectedCityName}}
        </p>
        <hr />
        
        <label>Hight performance. Handles even 10000 items.</label>
        <ng-select [items]="cities4"
                   [virtualScroll]="true"
                   bindLabel="name"
                   bindValue="id"
                   placeholder="Select city"
                   [(ngModel)]="selectedCityId">
        </ng-select>
        <p>
            Selected city ID: {{selectedCityId}}
        </p>
        <hr />
        
        <label>Append dropdown to body</label>
        <ng-select [items]="cities4"
                   bindLabel="name"
                   [virtualScroll]="true"
                   bindValue="id"
                   appendTo="body"
                   placeholder="Select city"
                   [(ngModel)]="selectedCityId">
        </ng-select>
        <p>
            Selected city ID: {{selectedCityId}}
        </p>
        <hr />
        
        <label>Grouping</label>
        <ng-select [items]="accounts"
                bindLabel="name"
                bindValue="name"
                groupBy="country"
                [(ngModel)]="selectedAccount">
        </ng-select>
        
        <div style="margin-top:300px"></div>
`
})
export class AppComponent {

    cities = [
        {id: 1, name: 'Vilnius'},
        {id: 2, name: 'Kaunas'},
        {id: 3, name: 'Pavilnys', disabled: true},
        {id: 4, name: 'Pabradė'},
        {id: 5, name: 'Klaipėda'}
    ];

    cities2 = [
        {id: 1, name: 'Vilnius'},
        {id: 2, name: 'Kaunas'},
        {id: 3, name: 'Pavilnys', disabled: true},
        {id: 4, name: 'Pabradė'},
        {id: 5, name: 'Klaipėda'}
    ];

    cities3 = [
        {id: 1, name: 'Vilnius', avatar: '//www.gravatar.com/avatar/b0d8c6e5ea589e6fc3d3e08afb1873bb?d=retro&r=g&s=30 2x'},
        {id: 2, name: 'Kaunas', avatar: '//www.gravatar.com/avatar/ddac2aa63ce82315b513be9dc93336e5?d=retro&r=g&s=15'},
        {id: 3, name: 'Pavilnys', avatar: '//www.gravatar.com/avatar/6acb7abf486516ab7fb0a6efa372042b?d=retro&r=g&s=15'}
    ];

    cities4 = [];

    users = [
        {id: 'anjmao', name: 'Anjmao'},
        {id: 'varnas', name: 'Tadeus Varnas'}
    ];

    selectedAccount = 'Adam'
    accounts = [
        { name: 'Adam', email: 'adam@email.com', age: 12, country: 'United States' },
        { name: 'Samantha', email: 'samantha@email.com', age: 30, country: 'United States' },
        { name: 'Amalie', email: 'amalie@email.com', age: 12, country: 'Argentina' },
        { name: 'Estefanía', email: 'estefania@email.com', age: 21, country: 'Argentina' },
        { name: 'Adrian', email: 'adrian@email.com', age: 21, country: 'Ecuador' },
        { name: 'Wladimir', email: 'wladimir@email.com', age: 30, country: 'Ecuador' },
        { name: 'Natasha', email: 'natasha@email.com', age: 54, country: 'Ecuador' },
        { name: 'Nicole', email: 'nicole@email.com', age: 43, country: 'Colombia' },
        { name: 'Michael', email: 'michael@email.com', age: 15, country: 'Colombia' },
        { name: 'Nicolás', email: 'nicole@email.com', age: 43, country: 'Colombia' }
    ];

    selectedCity: any;
    selectedCityIds: string[];
    selectedCityName = 'Vilnius';
    selectedCityId: number;
    selectedUserIds: number[];

    constructor() {
        this.create10kCities();
    }

    addCustomUser = (term) => ({id: term, name: term});

    private create10kCities() {
        this.cities4 = Array.from({length: 10000}, (value, key) => key)
            .map(val => ({
                id: val,
                name: `city ${val}`
            }));
    }
}
