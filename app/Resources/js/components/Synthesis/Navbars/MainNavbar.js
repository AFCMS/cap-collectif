import React from 'react';
import { Navbar } from 'react-bootstrap';
import MainNavbarHeader from './MainNavbarHeader';
import MainNavbarUser from './MainNavbarUser';
import MainNavbarSearch from './MainNavbarSearch';

const MainNavbar = React.createClass({
  displayName: 'MainNavbar',

  render() {
    return (
      <Navbar fixedTop fluid className="synthesis__main-navbar">
        <MainNavbarHeader />
        <MainNavbarSearch />
      </Navbar>
    );
  },

});

export default MainNavbar;
