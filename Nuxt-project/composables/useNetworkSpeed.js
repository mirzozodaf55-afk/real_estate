export const useNetworkSpeed = (high,middle,small,xsmall) => {
    const getConnection = () => {
      const conn = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
      return conn;
    }
  
    const getQuality = () => {
      const connection = getConnection();
  
      if (!connection || !connection.downlink) {
        return '360'; // fallback
      }
  
      const speed = connection.downlink; // Мбит/сек
      if (speed > 5) return high;
      if (speed > 2.5) return middle;
      if (speed > 1.5) return small;
      return xsmall;
    }
  
    return {
      getQuality
    }
  }
  