// @flow
import styled, { type StyledComponent } from 'styled-components';

export const Container: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  display: flex;
  flex-direction: column;
`;

export const Header: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  padding: 20px;
  background-color: #fff;
  color: #000;

  h2 {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
  }
`;

export const Content: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  padding: 15px;
`;
