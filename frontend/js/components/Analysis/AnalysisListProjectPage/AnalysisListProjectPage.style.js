// @flow
import styled, { type StyledComponent } from 'styled-components';

const AnalysisListProjectPageContainer: StyledComponent<{}, {}, HTMLDivElement> = styled.div`
  padding: 80px;

  h2 {
    font-size: 18px;
    font-weight: bold;
    color: #000;
    margin: 0 0 20px 0;
  }

  .project-analysis-preview {
    margin-bottom: 15px;
  }
`;

export default AnalysisListProjectPageContainer;
