// @flow
import styled, { type StyledComponent } from 'styled-components';
import Slider from 'react-slick';

export const Container: StyledComponent<{}, {}, typeof Slider> = styled(Slider)`
  width: 100%;

  .slick-track {
    display: flex;
    align-items: center;
  }

  .slick-slide {
    padding: 0 20px;

    a {
      display: block;
      text-decoration: none;

      &:hover {
        text-decoration: none;
      }
    }

    img {
      width: 100%;
    }
  }
`;
