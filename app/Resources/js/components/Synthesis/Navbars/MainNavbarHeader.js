import React from 'react';
import { Navbar } from 'react-bootstrap';

const MainNavbarHeader = React.createClass({
  displayName: 'MainNavbarHeader',

  render() {
    return (
      <Navbar.Header>
        <Navbar.Brand>
          <a href="/">
            <span style={{ color: '#A94442 !important' }}>Cap</span>{' '}
            <span style={{ color: '#000 !important' }}>Collectif</span>
          </a>
        </Navbar.Brand>
      </Navbar.Header>
    );
  }
});

export default MainNavbarHeader;
